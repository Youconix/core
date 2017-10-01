<?php

namespace youconix\core\auth;

use youconix\core\services\FileHandler AS FileHandler;
use youconix\core\repositories\User AS User;

class Auth extends \youconix\core\services\Service
{

  /**
   *
   * @var \youconix\core\services\FileHandler;
   */
  protected $fileHandler;

  /**
   *
   * @var \youconix\core\repositories\User $user
   */
  protected $user;

  /**
   *
   * @var \youconix\core\entities\User $user
   */
  protected $currentUser;

  /**
   *
   * @var \Headers
   */
  protected $headers;

  /**
   *
   * @var \Session
   */
  protected $session;

  /**
   *
   * @var \Config
   */
  protected $config;

  /**
   *
   * @var \Mailer
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
   * @param \youconix\core\services\FileHandler $fileHandler
   * @param \youconix\core\models\repositories\User $user
   * @param \Session $session
   * @param \Headers $headers
   * @param \Config $config
   * @param \Mailer $mailer
   */
  public function __construct(FileHandler $fileHandler, User $user,
			      \Session $session, \Headers $headers, \Config $config, \Mailer $mailer)
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
   * @param \stdClass $a_data
   * @return \youconix\core\entities\User
   */
  public function createUser(array $a_data = [])
  {
    $user = $this->user->createUser($a_data);
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

    $this->session->setLoginSession($user);
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
   * Null if the user is not logged in
   * 
   * @return \youconix\core\entities\User | null
   */
  public function getUser()
  {
    if (!is_null($this->currentUser)) {
      return $this->currentUser;
    }

    $i_userid = $this->session->get('userid');

    if ($i_userid == -1) {
      return null;
    }

    $user = $this->user->find($i_userid);
    if (is_null($user)) {
      return null;
    }

    $bo_bindToIp = $user->getBindToIp();
    if (!$this->session->exists('fingerprint') || ($this->session->get('fingerprint') != $this->session->getFingerprint($bo_bindToIp))) {
      return null;
    }

    $this->currentUser = $user;
    if (!defined('USERID')) {
      define('USERID', $user->getID());
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
    $this->session->set('fingerprint', $user->getFingerprint());
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

    $this->regenerate();
  }
}
