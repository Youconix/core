<?php

namespace youconix\core\auth\guards;

class Normal implements \Guard
{
  /**
   *
   * @var \Language
   */
  private $language;

  /**
   *
   * @var \Config
   */
  private $config;

  /**
   *
   * @var \youconix\core\services\Hashing
   */
  private $hashing;

  /**
   * 
   * @var \youconix\core\helpers\PasswordForm
   */
  private $form;

  /**
   *
   * @var \youconix\core\helpers\Captcha
   */
  private $captcha;

  /**
   *
   * @var \Builder
   */
  private $builder;

  /**
   * @var \youconix\core\auth\Auth 
   */
  private $auth;

  public function __construct(\Language $language, \Config $config,
                              \youconix\core\services\Hashing $hashing,
                              \Builder $builder,
                              \youconix\core\helpers\PasswordForm $form,
                              \youconix\core\helpers\Captcha $captcha)
  {
    $this->language = $language;
    $this->config = $config;
    $this->hashing = $hashing;
    $this->builder = $builder;
    $this->form = $form;
    $this->captcha = $captcha;
  }

  public function loginForm(\Output $output, \Input $request)
  {
    $this->auth->getHeaders()->http401();

    $s_form = '<h2>'.$this->language->get('login/button').'</h2>
	    <form action="/login/do_login/'.$this->getName().'" method="post">
	<table>
	  <tbody>
	    <tr>
	      <td><label>'.$this->language->get('system/admin/users/username').'</label></td>
	      <td><input type="text" name="username" value="'.$request->getDefault('username').'" required></td>
	    </tr>
	    <tr>
	      <td><label>'.$this->language->get('system/admin/users/password').'</label></td>
	      <td><input type="password" name="password" required></td>
	    </tr>
	    <tr>
	      <td><input type="checkbox" value="1" name="autologin" style="float:right"></td>
	      <td>'.$this->language->get('login/autologin').'</td>
	    </tr>
	    <tr>
	      <td colspan="2"><input type="submit" value="'.$this->language->get('login/button').'" class="button"></td>
	    </tr>
	    <tr>
	      <td colspan="2"><br></td>
	    </tr>
	    <tr>
	      <td><a href="/registration/'.$this->getName().'">'.$this->language->get('login/registration').'</a></td>
	      <td><a href="/password/'.$this->getName().'">Forgot password</a></td>
	    </tr>
	  </tbody>
	</table>
	</form>';

    $output->set('login_form', $s_form);
    $this->addJavascript($output);
  }

  public function do_login(\Input $request)
  {
    if (!$request->validate([
            'username' => 'type:string|required',
            'password' => 'type:string|required'
        ])) {
      return Normal::FORM_INVALID;
    }

    $s_username = $request->get('username');
    $s_plainPassword = $request->get('password');

    $this->builder->select('users', '*')->getWhere()->bindString('nick',
            $s_username)
        ->bindString('active', 1)->bindString('blocked', 0)->bindString('loginType',
        'normal');
    $database = $this->builder->getResult();
    if ($database->num_rows() == 0) {
      return Normal::INVALID_LOGIN;
    }
    $a_data = $database->fetch_object();
    $s_password = $a_data[0]->password;

    if (!$this->hashing->verify($s_plainPassword, $s_password)) {
      if (!$this->oldHashing($s_username, $s_plainPassword, $s_password)) {
        return Normal::INVALID_LOGIN;
      }
    }

    if ($a_data[0]->password_expired == 1) {
      $this->auth->getSession()->set('user_id', $a_data[0]->id);
      return Normal::LOGIN_EXPIRED;
    }

    $user = $this->auth->createUser($a_data[0]);
    $this->auth->setLogin($user);
  }

  private function oldHashing($s_username, $s_plainPassword, $stored)
  {
    $settings = $this->config->getSettings();
    $s_salt = $settings->get('settings/main/salt');

    $s_hash = sha1(substr(md5($s_username), 5, 30).$s_plainPassword.$s_salt);
    if ($s_hash == $stored) {
      $s_password = $this->hashing->hash($s_plainPassword);
      $this->builder->update('users')->bindString('password', $s_password)->getWhere('nick',
          $s_username);
      $this->builder->getResult();

      return true;
    }
    return false;
  }

  public function expiredForm(\Output $output)
  {
    $s_form = '<form action="/login/update/'.$this->getName().'" method="post">
      <h2>'.$this->language->get('login/editPassword').'</h2>
	
      <table>
	<tbody>
	  <tr>
	    <td><label>'.$this->language->get('login/currentPassword').' :</label></td>
	    <td><input type="password" name="password_old" required></td>
	  </tr>
	  <tr>
	    <td><label>'.$this->language->get('login/newPassword').' :</label></td>
	    <td><input type="password" name="password" required></td>
	  </tr>
	  <tr>
	    <td><label>'.$this->language->get('login/newPasswordAgain').' :</label></td>
	    <td><input type="password" name="password2" required></td>
	  </tr>
	  <tr>
	    <td colspan="2"><input type="submit" value="'.$this->language->get('login/editPassword').'" class="button"></td>
	  </tr>
	</tbody>
      </table>
      </form>';
    $output->set('expired_form', $s_form);
    $this->addJavascript($output);
  }

  public function updatePassword(\Input $request)
  {
    if (!$request->validate([
            'password_old' => 'type:string|required',
            'password' => 'type:string|required',
            'password2' => 'type:string|required'
        ])) {
      return Normal::FORM_INVALID;
    }

    $session = $this->auth->getSession();
    if (($request->get('password') !== $request->get('password2')) || (!$session->exists('userid'))) {
      return Normal::FORM_INVALID;
    }

    $i_userid = $session->get('userid');
    $s_password = $this->hashing->hash($this->post->get('password'));
    $s_passwordCurrent = $request->get('password_old');
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

  public function registrationForm(\Output $output, \Input $request)
  {
    $s_form = '<h2>'.$this->language->get('registration/screenTitle').'</h2>
	
    <form action="/registration/save/'.$this->getName().'" method="post" id="registration_form">
      <section>
	  <fieldset>
	    <label class="label">'.$this->language->get('system/admin/users/username').' *</label>
	    <td><input type="text" name="username" value="'.$request->getDefault('username').'" required data-validation="De gebruikersnaam is niet ingevuld" data-validation-taken="De gebruikersnaam is al in gebruik"></span>
	  </fieldset>
	  <fieldset>
	    <label class="label">'.$this->language->get('system/admin/users/email').' *</label>
	    <span><input type="email" name="email" value="'.$request->getDefault('email').'" required data-validation="Het E-mail adres is not correct" data-validation-taken="Het E-mail adres is al in gebruik"></span>
	  </fieldset>
	</section>
			
      '.$this->form->generate($output).'
	
      <section>
	<fieldset>
	    <span class="label"><input type="checkbox" name="conditions" id="reg_conditions" required data-validation="Je moet accoord gaan met de voorwaarden."></span>
	    <label><a href="/conditions/view" target="_new">'.$this->language->get('registration/conditions').'</a> *</label>
	  </fieldset>
      </section>

      <h2>'.$this->language->get('registration/captcha').' *</h2>
			
      <section>
	<fieldset>
	  <label><img src="/'.$this->config->getSharedStylesDir().'images/captcha.php?time='.time().'" id="registration_captcha" alt=""></label>
	  <span><img src="/'.$this->config->getSharedStylesDir().'images/reload.png" alt="reload" id="reload_captcha"/></span>
	</fieldset>
	<fieldset>
	  <span><input type="text" name="captcha" value="" style="width:80%" required data-validation="De captcha is niet ingevuld"></span>
	</fieldset>
	<fieldset>
	  <input type="submit" value="'.$this->language->get('registration/submitButton').'">
	</fieldset>
	</section>
	</form>';

    $output->set('registration_form', $s_form);
    $this->addJavascript($output);
  }

  public function do_registration(\Input $request)
  {
    if (!$request->validate([
            'username' => 'type:string|required',
            'email' => 'type:email|required',
            'password' => 'type:string|required',
            'password2' => 'type:string|required',
            'conditions' => 'required',
            'captcha' => 'type:string|required',
        ])) {
      return Normal::FORM_INVALID;
    }

    $s_username = $request->get('username');
    $s_email = $request->get('email');
    $s_password = $request->get('password');
    $s_password2 = $request->get('password2');
    $s_captcha = $request->get('captcha');

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

    $this->auth->getSession()->set('userid', $i_userid);
    $this->auth->getHeaders()->redirect('/login/expired/'.$this->getName());
  }

  public function email_confirm()
  {
    
  }

  public function getName()
  {
    return 'normal';
  }

  public function resetForm(\Output $output, \Input $request)
  {
    $s_form = '<form action="/password/do_reset/'.$this->getName().'" method="post">
      <h2>'.$this->language->get('forgotPassword/header').'</h2>
	
      <section>
		
      <fieldset>
	<label class="label">'.$this->language->get('registration/name').' :</label>
	<span><input type="text" name="username" value="'.$request->getDefault('username').'" required></span>
      </fieldset>
      <fieldset>
	<label class="label">'.$this->language->get('registration/email').' :</label>
	<span><input type="email" name="email" value="'.$request->getDefault('email').'" required></span>
      </fieldset>
      <fieldset>
	<input type="submit" value="'.$this->language->get('system/buttons/reset').'" class="button">
      </fieldset>
      
      </section>
    </form>';

    $output->set('reset_form', $s_form);
    $this->addJavascript($output);
  }

  public function hasRegistration()
  {
    return true;
  }

  public function hasReset()
  {
    return true;
  }

  public function sendResetEmail(\Input $request)
  {
    if (!$request->validate([
            'username' => 'type:string|required',
            'email' => 'type:string|required'
        ])) {
      return Normal::FORM_INVALID;
    }

    $s_email = $request->get('email');
    $s_username = $request->get('username');
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

  public function setAuth(\youconix\core\auth\Auth $auth)
  {
    $this->auth = $auth;
  }

  public function getLogo()
  {
    return '';
  }

  public function usernameAvailable($s_username)
  {
    $database = $this->builder->select('users', 'id')->getWhere()->bindString('nick',
            $s_username)->getResult();
    return ($database->num_rows() == 0);
  }

  public function emailAvailable($s_email)
  {
    $database = $this->builder->select('users', 'id')->getWhere()->bindString('email',
            $s_email)->getResult();
    return ($database->num_rows() == 0);
  }

  private function addJavascript($output)
  {
    $output->append('head',
        '<script src="/js/authorization/normal.js"></script>');
  }
}