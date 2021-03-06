<?php

namespace youconix\Core\Auth;

use youconix\Core\Services\FileHandler AS FileHandler;
use youconix\Core\Repositories\User AS User;

class Auth extends \youconix\Core\Services\AbstractService
{

  /**
   *
   * @var \youconix\Core\Services\FileHandler;
   */
  protected $fileHandler;

  /**
   *
   * @var \youconix\Core\Repositories\User $user
   */
  protected $user;

  /**
   *
   * @var \youconix\Core\Entities\User $user
   */
  protected $currentUser;

  /**
   *
   * @var \HeadersInterface
   */
  protected $headers;

  /**
   *
   * @var \SessionInterface
   */
  protected $session;

  /**
   *
   * @var \ConfigInterface
   */
  protected $config;

  /**
   *
   * @var \MailerInterface
   */
  protected $mailer;

  /**
   *
   * @var \Guard[]
   */
  protected $a_guards = [];
  protected $s_default;

  /**
   *
   * @param \youconix\Core\Services\FileHandler $fileHandler
   * @param \youconix\Core\Repositories\User $user
   * @param \SessionInterface $session
   * @param \HeadersInterface $headers
   * @param \ConfigInterface $config
   * @param \MailerInterface $mailer
   */
  public function __construct(FileHandler $fileHandler, User $user,
                              \SessionInterface $session, \HeadersInterface $headers, \ConfigInterface $config, \MailerInterface $mailer)
  {
    $this->fileHandler = $fileHandler;
    $this->user = $user;
    $this->session = $session;
    $this->headers = $headers;
    $this->config = $config;
    $this->mailer = $mailer;

    $this->availableGuards();
  }

  /**
   * Returns if the object should be treated as singleton
   *
   * @return boolean True if the object is a singleton
   */
  public static function isSingleton()
  {
    return true;
  }

  /**
   * Loads the availableGuards
   */
  protected function availableGuards()
  {
    $a_guards = $this->config->getGuards();
    $this->a_guards = [];
    foreach ($a_guards AS $s_guard) {
      $a_name = explode('\\', $s_guard);
      $s_name = strtolower(end($a_name));

      $guard = \Loader::inject($s_guard);
      if (!is_null($guard)) {
	$guard->setAuth($this);
	$this->a_guards[$s_name] = $guard;
      }
    }

    $this->s_default = $this->config->getDefaultGuard();
  }

  /**
   * Returns the guard with the given name
   * Leave empty for the default guard
   * 
   * @param string $s_name
   * @return \Guard
   * @throws \LogicException if the guard does not exist
   */
  public function getGuard($s_name = '')
  {
    if (empty($s_name)) {
      $s_name = $this->s_default;
    }

    if (!array_key_exists($s_name, $this->a_guards)) {
      throw new \LogicException('Guard ' . $s_name . ' does not exist.');
    }

    return $this->a_guards[$s_name];
  }

  /**
   * Returns the headers class
   * 
   * @return \Headers
   */
  public function getHeaders()
  {
    return $this->headers;
  }

  /**
   * Creates the user object
   * 
   * @param \stdClass $data
   * @return \youconix\core\entities\User
   */
  public function createUser(\stdClass $data = null)
  {
    $user = $this->user->createUser($data);
    return $user;
  }
  
  /**
   * 
   * @param \Input $input
   * @throws \ValidationException
   */
  protected function checkPasswords(\Input $input)
  {
    if ($input->get('password') == '' || $input->get('password') != $input->get('password2')) {
      throw new \ValidationException($this->language->get('widgets/passwordForm/invalid'));
    }
    
    $settings = $this->config->getPasswordSettings();

    $s_password = $input->get('password');
    $i_minimunLength = $settings->mimimunLength;
    switch ($settings->level) {
      case 1:
	if (strlen($s_password) < $i_minimunLength) {
	  $s_error = str_replace('[length]', $i_minimunLength,
			  'Uw wachtwoord moet minimaal uit [length] tekens bestaan');
	  throw new \ValidationException($s_error);
	}
	break;
      case 2:
	if ((strlen($s_password) < $i_minimunLength) || (!preg_match('/[a-zA-Z]/',
							      $s_password) || !preg_match('/[0-9]/', $s_password))) {
	  $s_error = str_replace('[length]', $i_minimunLength,
			  'Uw wachtwoord moet minimaal uit [length] tekens inclusief 1 cijfer bestaan');
	  throw new \ValidationException($s_error);
	}
	break;
      case 3:
	if ((strlen($s_password) < $i_minimunLength) || (!preg_match('/[a-zA-Z]/',
							      $s_password) || !preg_match('/[0-9]/', $s_password) || !preg_match('/^[a-zA-Z0-9]/',
										$s_password) )) {
	  $s_error = str_replace('[length]', $i_minimunLength,
			  'Uw wachtwoord moet minimaal uit [length] tekens inclusief 1 cijfer en 1 speciaal teken bestaan');
	  throw new \ValidationException($s_error);
	}
	break;
    }
  }
  
  /**
   * 
   * @param \Input $input
   * @return \youconix\core\entities\User
   * @throws \ValidationException
   */
  public function addUser(\Input $input) {
    $this->checkPasswords($input);
    
    $user = $this->createUser();
    $user->setUsername($input->get('username'));
    $user->setEmail($input->get('email'));
    $user->setBot((bool) $input->get('bot'));
    $user->updatePassword($input->get('password'));
    $user->setRegistrated(new \DateTime());
        
    return $user;
  }
  
  /**
   * 
   * @param \Input $input
   * @param \youconix\core\entities\User $user
   * @return \youconix\core\entities\User
   */
  public function editUser(\Input $input, \youconix\core\entities\User $user){
    $user->setEmail($input->get('email'));
    
    if ($input->has('bot')) {
      $user->setBot((bool) $input->get('bot'));
    }
    if ($input->has('blocked')) {
      $user->setBot((bool) $input->get('blocked'));
    }
    if ($input->has('password')) {
      $this->checkPasswords($input);
      $user->updatePassword($input->get('password'));
      
    }
    
    return $user;
  }

  /**
   * Sets the login
   * 
   * @param User $user
   */
  public function setLogin(\youconix\core\entities\User $user)
  {
    $s_redirection = $this->config->getLoginRedirect();

    if ($this->session->exists('page')) {
      if ($this->session->get('page') != 'logout.php') {
	$s_redirection = $this->session->get('page');
      }

      $this->session->delete('page');
    }

    while (strpos($s_redirection, '//') !== false) {
      $s_redirection = str_replace('//', '/', $s_redirection);
    }

    $this->setLoginSession($user);
    $user->updateLastLoggedIn();

    $this->headers->redirect($s_redirection);
  }

  /**
   * Sends the reset password email
   *
   * @param string $s_type
   * @param string $s_username
   * @param string $s_email
   * @param string $s_password
   * @param string $s_hash
   * @param int $i_expire
   */
  public function sendResetMail($s_type, $s_username, $s_email, $s_password,
				$s_hash, $i_expire)
  {
    $s_expire = date('d-m-Y H:i:s', $i_expire);
    $s_url = 'password/verifyCode/' . $s_type . '/' . $s_hash;

    $this->mailer->passwordResetMail($s_username, $s_email, $s_password, $s_url,
				     $s_expire);
  }

  /**
   * Sends the registration email
   *
   * @param string $s_type
   * @param string $s_username
   * @param string $s_email
   * @param string $s_hash
   */
  public function sendRegistrationMail($s_type, $s_username, $s_email, $s_hash)
  {
    $s_url = 'registration/activate/' . $s_type . '/' . $s_username . '/' . $s_hash;

    $this->mailer->registrationMail($s_url, $s_email, $s_url);
  }

  /**
   * Logs the user out
   */
  public function logout()
  {
    $this->destroyLogin();
    $this->headers->redirect($this->config->getLogoutRedirect());
  }

  /**
   * Returns all active guards
   * 
   * @return \Guards[]
   */
  public function getGuards()
  {
    return $this->a_guards;
  }

  /**
   * Returns the current logged in user
   * User object with userid NULL if the user is not logged in
   * 
   * @return \youconix\core\entities\User
   */
  public function getUser()
  {
    if (!is_null($this->currentUser)) {
      return $this->currentUser;
    }
    
    $this->currentUser = $this->user->createUser();
    $user = ($this->session->exists('userid') ? $this->user->find($this->session->get('userid')) : null);

    if (!is_null($user)) {
      $bo_bindToIp = $user->getBindToIp();
      if ($this->session->exists('fingerprint') && ($this->session->get('fingerprint') == $this->session->getFingerprint($bo_bindToIp))) {
	$this->currentUser = $user;
	if (!defined('USERID')) {
	  define('USERID', $user->getID());
	}
      }
    }

    return $this->currentUser;
  }

  /**
   * Logs the user in and sets the login-session
   *
   * @param \youconix\core\entities\User $user
   */
  protected function setLoginSession(\youconix\core\entities\User $user)
  {
    $this->destroyLogin();

    $this->session->set('login', '1');
    $this->session->set('userid', $user->getUserId());
    $this->session->set('username', $user->getUsername());
    $this->session->set('fingerprint', $this->session->getFingerprint($user->getBindToIp()));
    $this->session->set('lastLogin', $user->getLastLogin()->getTimestamp());
  }

  /**
   * Logs the admin in with the given user id and username
   * Admin session will be restored at logout
   * Destroys the current session array
   *
   * @param \youconix\core\entities\User $user
   */
  public function setLoginTakeover(\youconix\core\entities\User $user)
  {
    $a_lastLogin = array(
	'userid' => $this->session->get('userid'),
	'username' => $this->session->get('username'),
	'lastLogin' => $this->session->get('lastLogin')
    );

    $this->setLoginSession($user);
    $this->session->set('last_login', $a_lastLogin);
  }

  protected function destroyLogin()
  {
    $a_fields = [
	'login',
	'userid',
	'username',
	'fingerprint',
	'lastLogin',
	'type'
    ];

    foreach ($a_fields as $s_field) {
      if ($this->session->exists($s_field)) {
	$this->session->delete($s_field);
      }
    }

    $this->session->regenerate();
  }
}
