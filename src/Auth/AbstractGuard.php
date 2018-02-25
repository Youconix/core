<?php

namespace youconix\Core\Auth;

use youconix\Core\Input;
use youconix\Core\Auth\Auth;

abstract class AbstractGuard implements \GuardInterface
{

  /**
   *
   * @var \LanguageInterface
   */
  protected $language;

  /**
   *
   * @var \SettingsInterface
   */
  protected $settings;

  /**
   *
   * @var \BuilderInterface
   */
  protected $builder;

  /**
   * @var \youconix\Core\Auth\Auth
   */
  protected $auth;

  /**
   *
   * @var \SessionInterface
   */
  protected $session;

  /**
   *
   * @var boolean
   */
  protected $enabled = false;

  /**
   *
   * @var array
   */
  protected $guardConfig = ['enabled' => true];

  /**
   * 
   * @param \LanguageInterface $language
   * @param \SettingsInterface $settings
   * @param \BuilderInterface $builder
   * @param \SessionInterface $session
   */
  public function __construct(\LanguageInterface $language, \SettingsInterface $settings,
                              \BuilderInterface $builder, \SessionInterface $session)
  {
    $this->language = $language;
    $this->settings = $settings;
    $this->builder = $builder;
    $this->session = $session;

    $this->loadConfig();
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

  protected function loadConfig()
  {
    $path = 'auth/' . $this->getName();
    if (!$this->settings->exists($path)) {
      return;
    }

    $items = $this->settings->getBlock($path);
    foreach ($items as $key => $value) {
      $this->guardConfig[$key] = $value;
    }
  }

  /**
   * 
   * @return boolean
   */
  abstract public function hasReset();

  /**
   * 
   * @return boolean
   */
  abstract public function hasActivation();

  /**
   * 
   * @return boolean
   */
  public function isRegistrationEnabled()
  {
    return $this->settings->get('settings/auth/usersRegister');
  }

  /**
   * 
   * @return boolean
   */
  abstract public function hasConfig();

  /**
   * @param Input $config
   */
  public function validate(Input $config)
  {
    $keys = array_keys($this->guardConfig);
    $name = $this->getName();

    foreach ($keys as $key) {
      if (!$config->has($name . '_' . $key)) {
	return false;
      }
    }
    return true;
  }

  /**
   * 
   * @param Input $config
   */
  public function setConfig(Input $config)
  {
    $keys = array_keys($this->guardConfig);
    $name = $this->getName();
    $path = 'auth/' . $this->getName();

    $this->guardConfig = [];
    foreach ($keys as $key) {
      $this->guardConfig[$key] = $config->get($name . '_' . $key);
      $this->settings->set($path . '/' . $key, $this->guardConfig[$key]);
    }
  }

  /**
   * 
   * @return array
   */
  public function isEnabled()
  {
    return $this->guardConfig['enabled'];
  }

  /**
   * 
   * @param boolean $enabled
   */
  public function setEnabled($enabled)
  {
    $this->guardConfig['enabled'] = $enabled;
  }

  /**
   * 
   * @param Auth $auth
   */
  public function setAuth(Auth $auth)
  {
    $this->auth = $auth;
  }

  /**
   * 
   * @return string
   */
  abstract public function getLogo();

  /**
   * 
   * @param string $s_username
   * @return boolean
   */
  public function usernameAvailable($s_username)
  {
    $database = $this->builder->select('users', 'id')->getWhere()->bindString('nick',
									      $s_username)->getResult();
    return ($database->num_rows() == 0);
  }

  /**
   * 
   * @param string $s_email
   * @return boolean
   */
  public function emailAvailable($s_email)
  {
    $database = $this->builder->select('users', 'id')->getWhere()->bindString('email',
									      $s_email)->getResult();
    return ($database->num_rows() == 0);
  }
}
