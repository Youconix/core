<?php

namespace youconix\core\auth\guards;

class Normal extends \youconix\core\auth\PasswordGuard
{

  /**
   *
   * @var \youconix\core\services\Hashing
   */
  protected $hashing;

  /**
   * 
   * @var \youconix\core\helpers\PasswordForm
   */
  protected $form;

  /**
   *
   * @var \youconix\core\helpers\Captcha
   */
  protected $captcha;

  /**
   * 
   * @param \LanguageInterface $language
   * @param \SettingsInterface $settings
   * @param \youconix\core\services\Hashing $hashing
   * @param \Builder $builder
   * @param \youconix\core\helpers\PasswordForm $form
   * @param \youconix\core\helpers\Captcha $captcha
   * @param \Session $session
   */
  public function __construct(\LanguageInterface $language, \SettingsInterface $settings,
                              \youconix\core\services\Hashing $hashing, \Builder $builder,
                              \youconix\core\helpers\PasswordForm $form,
                              \youconix\core\helpers\Captcha $captcha, \Session $session)
  {
    parent::__construct($language, $settings, $builder, $session);

    $this->hashing = $hashing;
    $this->form = $form;
    $this->captcha = $captcha;
  }

  protected function loadConfig()
  {
    $this->guardConfig = [
	'enabled' => false,
	'length' => 8,
	'strength' => 1
    ];

    parent::loadConfig();
  }

  /**
   * 
   * @return boolean
   */
  public function hasConfig()
  {
    return true;
  }

  /**
   * 
   * @param \OutputInterface $output
   * @param \Request $request
   */
  public function loginForm(\OutputInterface $output, \Request $request)
  {
    $this->auth->getHeaders()->http401();
    $post = $request->post();

    $s_form = '<h2>' . $this->language->get('login/button') . '</h2>
	    <form action="path(\'login_do_login\', {\'type\': \'' . $this->getName() . '\'})" method="post">
	<table>
	  <tbody>
	    <tr>
	      <td><label>' . $this->language->get('system/admin/users/username') . '</label></td>
	      <td><input type="text" name="username" value="' . $post->getDefault('username') . '" required></td>
	    </tr>
	    <tr>
	      <td><label>' . $this->language->get('system/admin/users/password') . '</label></td>
	      <td><input type="password" name="password" required></td>
	    </tr>
	    <tr>
	      <td><input type="checkbox" value="1" name="autologin" style="float:right"></td>
	      <td>' . $this->language->get('login/autologin') . '</td>
	    </tr>
	    <tr>
	      <td colspan="2"><input type="submit" value="' . $this->language->get('login/button') . '" class="button"></td>
	    </tr>
	    <tr>
	      <td colspan="2"><br></td>
	    </tr>
	    <tr>
	      <td><a href="path(\'registration_view\', {\'name\' : \'' . $this->getName() . '\'})">' . $this->language->get('login/registration') . '</a></td>
	      <td><a href="path(\'password_screen\', {\'name\' : \'' . $this->getName() . '\'})">Forgot password</a></td>
	    </tr>
	  </tbody>
	</table>
	</form>';

    $output->set('login_form', $s_form);
    $this->addJavascript($output);
  }

  /**
   * 
   * @param \Request $request
   * @return string
   */
  public function do_login(\Request $request)
  {
    $post = $request->post();

    if (!$post->validate([
	    'username' => 'type:string|required',
	    'password' => 'type:string|required'
	])) {
      return Normal::FORM_INVALID;
    }

    $s_username = $post->get('username');
    $s_plainPassword = $post->get('password');

    $this->builder->select('users', '*')
	->getWhere()
	->bindString('nick', $s_username)
	->bindString('active', 1)
	->bindString('blocked', 0)
	->bindString('loginType', 'normal');
    $database = $this->builder->getResult();
    if ($database->num_rows() == 0) {
      return Normal::INVALID_LOGIN;
    }
    $data = $database->fetch_object();
    $s_password = $data[0]->password;

    if (!$this->hashing->verify($s_plainPassword, $s_password)) {
      if (!$this->oldHashing($s_username, $s_plainPassword, $s_password)) {
	return Normal::INVALID_LOGIN;
      }
    }

    if ($data[0]->password_expired == 1) {
      $this->session->set('user_id', $data[0]->id);
      return Normal::LOGIN_EXPIRED;
    }

    $data[0]->username = $data[0]->nick;
    $data[0]->userid = $data[0]->id;

    $user = $this->auth->createUser($data[0]);
    $this->auth->setLogin($user);
  }

  /**
   * 
   * @param string $s_username
   * @param string $s_plainPassword
   * @param string $stored
   * @return boolean
   */
  private function oldHashing($s_username, $s_plainPassword, $stored)
  {
    $settings = $this->config->getSettings();
    $s_salt = $settings->get('settings/main/salt');

    $s_hash = sha1(substr(md5($s_username), 5, 30) . $s_plainPassword . $s_salt);
    if ($s_hash == $stored) {
      $s_password = $this->hashing->hash($s_plainPassword);
      $this->builder->update('users')->bindString('password', $s_password)->getWhere('nick',
										     $s_username);
      $this->builder->getResult();

      return true;
    }
    return false;
  }

  /**
   * 
   * @param \OutputInterface $output
   */
  public function expiredForm(\OutputInterface $output)
  {
    $s_form = '<form action="/login/update/' . $this->getName() . '" method="post">
      <h2>' . $this->language->get('login/editPassword') . '</h2>
	
      <table>
	<tbody>
	  <tr>
	    <td><label>' . $this->language->get('login/currentPassword') . ' :</label></td>
	    <td><input type="password" name="password_old" required></td>
	  </tr>
	  <tr>
	    <td><label>' . $this->language->get('login/newPassword') . ' :</label></td>
	    <td><input type="password" name="password" required></td>
	  </tr>
	  <tr>
	    <td><label>' . $this->language->get('login/newPasswordAgain') . ' :</label></td>
	    <td><input type="password" name="password2" required></td>
	  </tr>
	  <tr>
	    <td colspan="2"><input type="submit" value="' . $this->language->get('login/editPassword') . '" class="button"></td>
	  </tr>
	</tbody>
      </table>
      </form>';
    $output->set('expired_form', $s_form);
    $this->addJavascript($output);
  }

  /**
   * 
   * @param \Request $request
   * @return string
   */
  public function updatePassword(\Request $request)
  {
    $post = $request->post();

    if (!$post->validate([
	    'password_old' => 'type:string|required',
	    'password' => 'type:string|required',
	    'password2' => 'type:string|required'
	])) {
      return Normal::FORM_INVALID;
    }

    if (($post->get('password') !== $post->get('password2')) || (!$this->session->exists('userid'))) {
      return Normal::FORM_INVALID;
    }

    $i_userid = $this->session->get('userid');
    $s_password = $this->hashing->hash($post->get('password'));
    $s_passwordCurrent = $post->get('password_old');
    $this->builder->update('users')->bindString('password', $s_password)->getWhere()->bindInt('userid',
											      $i_userid)->bindString('password', $s_passwordCurrent);
    $database = $this->builder->getResult();
    if ($database->affected_rows() == 0) {
      return Normal::FORM_INVALID;
    }

    $database = $this->builder->select('users', '*')->getWhere()->bindInt('userid',
									  $i_userid)->getResult();
    $a_data = $database->fetch_object();
    $user = $this->auth->createUser($a_data[0]);
    $this->auth->setLogin($user);
  }

  /**
   * 
   * @param \OutputInterface $output
   * @param \Request $request
   */
  public function registrationForm(\OutputInterface $output, \Request $request)
  {
    $post = $request->post();

    $s_form = '<h2>' . $this->language->get('registration/screenTitle') . '</h2>
	
    <form action="/registration/save/' . $this->getName() . '" method="post" id="registration_form">
      <section>
	  <fieldset>
	    <label class="label">' . $this->language->get('system/admin/users/username') . ' *</label>
	    <td><input type="text" name="username" value="' . $post->getDefault('username') . '" required data-validation="De gebruikersnaam is niet ingevuld" data-validation-taken="De gebruikersnaam is al in gebruik"></span>
	  </fieldset>
	  <fieldset>
	    <label class="label">' . $this->language->get('system/admin/users/email') . ' *</label>
	    <span><input type="email" name="email" value="' . $post->getDefault('email') . '" required data-validation="Het E-mail adres is not correct" data-validation-taken="Het E-mail adres is al in gebruik"></span>
	  </fieldset>
	</section>
			
      ' . $this->form->generate($output) . '
	
      <section>
	<fieldset>
	    <span class="label"><input type="checkbox" name="conditions" id="reg_conditions" required data-validation="Je moet accoord gaan met de voorwaarden."></span>
	    <label><a href="path(\'conditions_view\')" target="_new">' . $this->language->get('registration/conditions') . '</a> *</label>
	  </fieldset>
      </section>

      <h2>' . $this->language->get('registration/captcha') . ' *</h2>
			
      <section>
	<fieldset>
	  <label><img src="/' . $this->config->getSharedStylesDir() . 'images/captcha.php?time=' . time() . '" id="registration_captcha" alt=""></label>
	  <span><img src="/' . $this->config->getSharedStylesDir() . 'images/reload.png" alt="reload" id="reload_captcha"/></span>
	</fieldset>
	<fieldset>
	  <span><input type="text" name="captcha" value="" style="width:80%" required data-validation="De captcha is niet ingevuld"></span>
	</fieldset>
	<fieldset>
	  <input type="submit" value="' . $this->language->get('registration/submitButton') . '">
	</fieldset>
	</section>
	</form>';

    $output->set('registration_form', $s_form);
    $this->addJavascript($output);
  }

  /**
   * 
   * @param \Request $request
   * @return string
   */
  public function do_registration(\Request $request)
  {
    $post = $request->post();

    if (!$post->validate([
	    'username' => 'type:string|required',
	    'email' => 'type:email|required',
	    'password' => 'type:string|required',
	    'password2' => 'type:string|required',
	    'conditions' => 'required',
	    'captcha' => 'type:string|required',
	])) {
      return Normal::FORM_INVALID;
    }

    $s_username = $post->get('username');
    $s_email = $post->get('email');
    $s_password = $post->get('password');
    $s_password2 = $post->get('password2');
    $s_captcha = $post->get('captcha');

    if (($s_password != $s_password2) || !$this->captcha->checkCaptcha($s_captcha)) {
      return Normal::FORM_INVALID;
    }

    if (!$this->usernameAvailable($s_username)) {
      return Normal::USERNAME_TAKEN;
    }

    if (!$this->emailAvailable($s_email)) {
      return Normal::EMAIL_TAKEN;
    }

    $s_hash = $this->hashing->createRandom();
    $user = $this->auth->createUser();
    $user->setActivation($s_hash);
    $user->setEmail($s_email);
    $user->setLoginType('normal');
    $user->setPassword($s_password);
    $user->setUsername($s_username);
    $user->save();

    $this->auth->sendRegistrationMail($this->getName(), $s_username, $s_email,
				      $s_hash);

    return Normal::FORM_OKE;
  }

  /**
   * 
   * @param string $hash
   * @return string
   */
  public function do_reset($hash)
  {
    $database = $this->builder->select('password_codes', '*')->getWhere()->bindString('code',
										      $hash)
	    ->bindInt('expire', time(), 'AND', '>=')->getResult();
    if ($database->num_rows() == 0) {
      return Normal::FORM_INVALID;
    }

    $i_userid = $database->result(0, 'userid');
    $s_password = $this->hashing->hash($database->result(0, 'password'));
    $this->builder->delete('password_codes')->getWhere()->bindInt('userid',
								  $i_userid)->getResult();
    $this->builder->update('users')->bindString('password', $s_password)->bindString('password_expired',
										     '1')
	->bindString('blocked', '0')->getWhere()->bindInt('id', $i_userid)->getResult();

    $this->session->set('userid', $i_userid);
    $this->auth->getHeaders()->redirect('/login/expired/' . $this->getName());
  }

  public function email_confirm()
  {
    
  }

  /**
   * 
   * @return string
   */
  public function getName()
  {
    return 'normal';
  }

  /**
   * @return string
   */
  public function getDisplayName()
  {
    return 'Username & password';
  }

  /**
   * 
   * @param \OutputInterface $output
   * @param \Request $request
   */
  public function resetForm(\OutputInterface $output, \Request $request)
  {
    $post = $request->post();

    $s_form = '<form action="/password/do_reset/' . $this->getName() . '" method="post">
      <h2>' . $this->language->get('forgotPassword/header') . '</h2>
	
      <section>
		
      <fieldset>
	<label class="label">' . $this->language->get('registration/name') . ' :</label>
	<span><input type="text" name="username" value="' . $post->getDefault('username') . '" required></span>
      </fieldset>
      <fieldset>
	<label class="label">' . $this->language->get('registration/email') . ' :</label>
	<span><input type="email" name="email" value="' . $post->getDefault('email') . '" required></span>
      </fieldset>
      <fieldset>
	<input type="submit" value="' . $this->language->get('system/buttons/reset') . '" class="button">
      </fieldset>
      
      </section>
    </form>';

    $output->set('reset_form', $s_form);
    $this->addJavascript($output);
  }

  /**
   * 
   * @param \Request $request
   * @return string
   */
  public function sendResetEmail(\Request $request)
  {
    $post = $request->post();

    if (!$post->validate([
	    'username' => 'type:string|required',
	    'email' => 'type:string|required'
	])) {
      return Normal::FORM_INVALID;
    }

    $s_email = $post->get('email');
    $s_username = $post->get('username');
    $this->builder->select('users', 'id')->getWhere()->bindString('email',
								  $s_email)->bindString('nick', $s_username);
    $database = $this->builder->getResult();
    if ($database->num_rows() == 0) {
      return Normal::FORM_INVALID;
    }

    $s_hash = $this->hashing->createSalt();
    $i_userid = $database->result(0, 'id');
    $s_password = $this->hashing->createRandom();
    $i_expire = (time() + 3600);
    $this->builder->delete('password_codes')->getWhere()->bindInt('userid',
								  $i_userid)->getResult();
    $this->builder->insert('password_codes')->bindInt('userid', $i_userid)->bindString('code',
										       $s_hash)
	->bindString('password', $s_password)->bindInt('expire', $i_expire)->getResult();

    $this->auth->sendResetMail($this->getName(), $s_username, $s_email,
			       $s_password, $s_hash, $i_expire);

    return Normal::FORM_OKE;
  }

  /**
   * 
   * @param \OutputInterface $output
   */
  private function addJavascript(\OutputInterface $output)
  {
    $output->append('head',
		    '<script src="/js/authorization/normal.js"></script>');
  }

  /**
   * 
   * @return string
   */
  public function getConfigForm()
  {
    $name = $this->getName();

    $form = '<h3> Password requirements</h3>
      
    <fieldset>
	<label class="label">Minimun length</label>	
	<input type="range" name="' . $name . '_length" value="' . $this->guardConfig['length'] . '" min="8" max="68" required>	
	<span id="' . $name . '_length_display">' . $this->guardConfig['length'] . '</span>
    </fieldset>
    <fieldset>
	<label class="label">Demands</label>
	' . $this->createStrengthRadio(1) . ' <label>No extra requirements</label>
    </fieldset>
    <fieldset>
	<label class="label"></label>
	' . $this->createStrengthRadio(2) . ' <label>Letters and numbers</label>
    </fieldset>
    <fieldset>
	<label class="label"></label>
	' . $this->createStrengthRadio(3) . ' <label>Letters, numbers and special characters</label>
    </fieldset>
    
    <script>
    let rangeField = $("input[name=' . $name . '_length]");
    rangeField.on("change",() => {
      $("#' . $name . '_length_display").text(rangeField.val());
    });
    rangeField.on("input", () => {
      rangeField.trigger("change");
    });
    </script>';

    return $form;
  }

  /**
   * 
   * @param int $value
   * @return string
   */
  protected function createStrengthRadio($value)
  {
    $name = $this->getName();
    $selected = (($value == $this->guardConfig['strength']) ? 'checked="checked"' : '');

    return '<input type="radio" name="' . $name . '_strength" value="' . $value . '" ' . $selected . ' required>';
  }
}
