<?php

namespace youconix\core\auth;

use \youconix\core\services\FileHandler AS FileHandler;
use \youconix\core\models\data\User AS User;

class Auth extends \youconix\core\services\Service
{
  /**
   *
   * @var \youconix\core\services\FileHandler;
   */
  protected $fileHandler;

  /**
   *
   * @var \youconix\core\models\data\User $user
   */
  protected $user;

  /**
   *
   * @var \youconix\core\models\data\User $user
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
   * @param \youconix\core\models\data\User $user
   * @param \Session $session
   * @param \Headers $headers
   * @param \Config $config
   * @param \Mailer $mailer
   */
  public function __construct(FileHandler $fileHandler, User $user,
                              \Session $session, \Headers $headers,
                              \Config $config, \Mailer $mailer)
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
   * Returns if the object schould be treated as singleton
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
      throw new \LogicException('Guard '.$s_name.' does not exist.');
    }

    return $this->a_guards[$s_name];
  }

  /**
   * Returns the session class
   * 
   * @return \Session
   */
  public function getSession()
  {
    return $this->session;
  }

  /**
   * Rerturns the headers class
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
   * @return \youconix\core\models\data\User
   */
  public function createUser($a_data = null)
  {
    $user = clone $this->user;
    if (!is_null($a_data)) {
      $user->setData($a_data);
    }
    return $user;
  }

  /**
   * Sets the login
   * 
   * @param User $user
   */
  public function setLogin(\youconix\core\models\data\User $user)
  {
    $s_redirection = $this->config->getLoginRedirect();

    if ($this->session->exists('page')) {
      if ($this->session->get('page') != 'logout.php')
          $s_redirection = $this->session->get('page');

      $this->session->delete('page');
    }

    while (strpos($s_redirection, '//') !== false) {
      $s_redirection = str_replace('//', '/', $s_redirection);
    }

    $i_lastLogin = $user->lastLoggedIn();
    $user->updateLastLoggedIn();

    $this->session->setLogin($user->getID(), $user->getUsername(), $i_lastLogin,
        $user->isBindedToIp());
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
    $s_url = 'password/verifyCode/'.$s_type.'/'.$s_hash;

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
    $s_url = 'registration/activate/'.$s_type.'/'.$s_username.'/'.$s_hash;

    $this->mailer->registrationMail($s_url, $s_email, $s_url);
  }

  /**
   * Logs the user out
   */
  public function logout()
  {
    $this->session->destroyLogin();
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
   * @return \youconix\core\models\data\User | null
   */
  public function getUser()
  {
    if (!$this->session->exists('userid') || !$this->session->exists('fingerprint')) {
      return null;
    }

    $i_userid = $this->session->get('userid');
    $s_fingerprint = $this->session->get('fingerprint');
    if ($s_fingerprint != $this->session->getFingerprint()) {
      return null;
    }

    if (!is_null($this->currentUser)) {
      return $this->currentUser;
    }

    $a_users = $this->user->find(['id' => $i_userid]);
    if (count($a_users) == 0) {
      return null;
    }
    $this->currentUser = $a_users[0];
    if (!defined('USERID')) {
      define('USERID', $this->currentUser->getID());
    }

    return $this->currentUser;
  }
}