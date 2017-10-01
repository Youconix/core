<?php

namespace youconix\core;

/**
 * Config contains the main runtime configuration of the framework.
 *
 * @since 2.0
 */
class Config implements \Config
{

  /**
   *
   * @var \youconix\core\services\FileHandler
   */
  protected $file;

  /**
   *
   * @var \Settings
   */
  protected $settings;

  /**
   *
   * @var \Cookie
   */
  protected $cookie;

  /**
   *
   * @var \Builder
   */
  protected $builder;

  /**
   * 
   * @var string
   */
  protected $templateDir;

  /**
   *
   * @var string
   */
  protected $stylesDir;

  /**
   * 
   * @var bool
   */
  protected $_ajax = false;

  /**
   *
   * @var string
   */
  protected $base;

  /**
   *
   * @var string
   */
  protected $protocol;

  const LOG_MAX_SIZE = 10000000;

  /**
   *
   * @var string
   */
  protected $language;

  /**
   *
   * @var string
   */
  protected $s_page;

  /**
   *
   * @var string
   */
  protected $s_command;

  /**
   *
   * @var string
   */
  protected $s_templateDir;

  /**
   * PHP 5 constructor
   *
   * @param \youconix\core\services\FileHandler $file            
   * @param \Settings $settings            
   * @param \Cookie $cookie            
   */
  public function __construct(\youconix\core\services\FileHandler $file, \Settings $settings, \Cookie $cookie, \Builder $builder)
  {
    $this->file = $file;
    $this->settings = $settings;
    $this->cookie = $cookie;
    $this->builder = $builder;

    $this->loadLanguage();

    $this->setDefaultValues($settings);
  }

  /**
   * Sets the current page and command.
   * Called from the Router
   * 
   * @param string $s_page
   * @param string $s_command
   */
  public function setCall($s_page, $s_command)
  {
    if (substr($s_page, 0, 1) == '/') {
      $s_page = substr($s_page, 1);
    }
    if (substr($s_page, -4) != '.php') {
      $s_page .= '.php';
    }
    if (substr($s_command, 0, 1) == '/') {
      $s_command = substr($s_command, 1);
    }

    $this->s_page = $s_page;
    $this->s_command = $s_command;

    $this->detectTemplateDir();
  }

  /**
   * Returns the current page
   * 
   * @return string
   */
  public function getPage()
  {
    return $this->s_page;
  }

  /**
   * Returns the current command
   * 
   * @return string
   */
  public function getCommand()
  {
    return $this->s_command;
  }

  /**
   * Returns the default template directory
   * 
   * @return string
   */
  public function getTemplateDir()
  {
    return $this->s_templateDir;
  }

  /**
   * Returns the settings service
   *
   * @return \Settings The service
   */
  public function getSettings()
  {
    return $this->settings;
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
   * Adds the observer
   *
   * @see SplSubject::attach()
   */
  public function attach(\SplObserver $observer)
  {
    $this->a_observers->attach($observer);
  }

  /**
   * Removes the observer
   *
   * @see SplSubject::detach()
   */
  public function detach(\SplObserver $observer)
  {
    $this->a_observers->detach($observer);
  }

  /**
   * Notifies the observers
   *
   * @see SplSubject::notify()
   */
  public function notify()
  {
    foreach ($this->a_observers as $observer) {
      $observer->update($this);
    }
  }

  /**
   * Loads the language
   */
  protected function loadLanguage()
  {
    /* Check language */
    $a_languages = $this->getLanguages();
    $this->language = $this->settings->get('defaultLanguage');

    if (isset($_GET['lang'])) {
      if (in_array($_GET['lang'], $a_languages)) {
	$this->language = $_GET['lang'];
	$this->cookie->set('language', $this->language, '/');
      }
      unset($_GET['lang']);
    } else {
      if ($this->cookie->exists('language')) {
	if (in_array($this->cookie->get('language'), $a_languages)) {
	  $this->language = $this->cookie->get('language');
	  /* Renew cookie */
	  $this->cookie->set('language', $this->language, '/');
	} else {
	  $this->cookie->delete('language', '/');
	}
      }
    }
  }

  /**
   * Collects the installed languages
   *
   * @return array The installed languages
   */
  public function getLanguages()
  {
    $a_languages = array();
    $obj_languageFiles = $this->file->readDirectory(NIV . 'language');

    foreach ($obj_languageFiles as $languageFile) {
      $s_languageFile = $languageFile->getFilename();
      if (strpos($s_languageFile, 'language_') !== false) {
	/* Fallback */
	return $this->getLanguagesOld();
      }

      if ($s_languageFile == '..' || $s_languageFile == '.' || strpos($s_languageFile, '.') !== false) {
	continue;
      }

      $a_languages[] = $s_languageFile;
    }

    return $a_languages;
  }

  /**
   * Collects the installed languages
   * Old way of storing
   *
   * @return array The installed languages
   */
  protected function getLanguagesOld()
  {
    $a_languages = array();
    $a_languageFiles = $this->file->readDirectory(NIV . 'include/language');

    foreach ($a_languageFiles as $s_languageFile) {
      if (strpos($s_languageFile, 'language_') === false)
	continue;

      $s_languageFile = str_replace(array(
	  'language_',
	  '.lang'
	  ), array(
	  '',
	  ''
	  ), $s_languageFile);

      $a_languages[] = $s_languageFile;
    }

    $this->bo_fallback = true;

    return $a_languages;
  }

  /**
   * Sets the default values
   *
   * @param core\services\Settings $settings
   *            The settings service
   */
  protected function setDefaultValues($settings)
  {
    if (!defined('DB_PREFIX')) {
      define('DB_PREFIX', $settings->get('settings/SQL/prefix'));
    }

    $s_base = $settings->get('settings/main/base');
    if (substr($s_base, 0, 1) != '/') {
      $this->base = '/' . $s_base;
    } else {
      $this->base = $s_base;
    }

    if (!defined('BASE')) {
      define('BASE', NIV);
    }

    /* Get protocol */
    $this->protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";

    $this->detectAjax();

    if (!defined('LEVEL')) {
      define('LEVEL', '/');
    }

    if (!defined('WEBSITE_ROOT')) {
      define('WEBSITE_ROOT', $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $this->base);
    }
  }

  /**
   * Detects the template directory
   */
  protected function detectTemplateDir()
  {
    if (substr($this->getPage(), 0, 4) == 'admin') {
      $this->s_templateDir = $this->settings->get('templates/admin_dir');
    } else if ($this->isMobile()) {
      $this->s_templateDir = $this->settings->get('templates/mobile_dir');
    } else {
      $this->s_templateDir = $this->settings->get('templates/default_dir');
    }
  }

  /**
   * Detects an AJAX call
   */
  protected function detectAjax()
  {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
      $this->ajax = true;
    } else
    if (function_exists('apache_request_headers')) {
      $a_headers = apache_request_headers();
      $this->ajax = (isset($a_headers['X-Requested-With']) && $a_headers['X-Requested-With'] == 'XMLHttpRequest');
    }
    if (!$this->ajax && ((isset($_GET['AJAX']) && $_GET['AJAX'] == 'true') || (isset($_POST['AJAX']) && $_POST['AJAX'] == 'true'))) {
      $this->ajax = true;
    }
  }

  /**
   * Returns the shared style directory
   *
   * @return string
   */
  public function getSharedStylesDir()
  {
    return 'styles/shared/';
  }

  /**
   * Returns the current language from the user
   *
   * @return string The language code
   */
  public function getLanguage()
  {
    return $this->language;
  }

  /**
   * Returns the used protocol
   *
   * @return string
   */
  public function getProtocol()
  {
    return $this->protocol;
  }

  /**
   * Checks if the connection is via SSL/TSL
   *
   * @return bool True if the connection is encrypted
   */
  public function isSLL()
  {
    return ($this->getProtocol() == 'https://');
  }

  /**
   * Checks if ajax-mode is active
   *
   * @return boolean True if ajax-mode is active
   */
  public function isAjax()
  {
    return $this->ajax;
  }

  /**
   * Sets the framework in ajax-mode
   */
  public function setAjax()
  {
    $this->ajax = true;
  }

  /**
   * Returns the server host
   *
   * @return string 
   */
  public function getHost()
  {
    return $_SERVER['HTTP_HOST'];
  }

  /**
   * Returns the path to the website root
   * This value gets set in {LEVEL}
   *
   * @return string
   */
  public function getBase()
  {
    return $this->base;
  }

  /**
   * Returns the login redirect url
   *
   * @return string
   */
  public function getLoginRedirect()
  {
    $s_page = $this->getBase() . 'index/view';

    if ($this->settings->exists('login/login')) {
      $s_page = $this->getBase() . $this->settings->get('login/login');
    }

    return $s_page;
  }

  /**
   * Returns the logout redirect url
   *
   * @return string The url
   */
  public function getLogoutRedirect()
  {
    $s_page = $this->getBase() . 'index/view';

    if ($this->settings->exists('login/logout')) {
      $s_page = $this->getBase() . $this->settings->get('login/logout');
    }

    return $s_page;
  }

  /**
   * Returns the registration redirect url
   *
   * @return string The url
   */
  public function getRegistrationRedirect()
  {
    $s_page = $this->getBase() . 'index/view';

    if ($this->settings->exists('login/registration')) {
      $s_page = $this->getBase() . $this->settings->get('login/registration');
    }

    return $s_page;
  }

  /**
   * Returns the authorisation guards
   *
   * @return array
   */
  public function getGuards()
  {
    $guardsBlock = $this->settings->getBlock('auth/guards');
    $guards = [];

    foreach ($guardsBlock AS $guardsRaw) {
      foreach ($guardsRaw->childNodes AS $guard) {
	$guards[] = $guard->nodeValue;
      }
    }

    return $guards;
  }

  /**
   * Returns the default authorisation guard
   * 
   * @return string
   */
  public function getDefaultGuard()
  {
    return $this->settings->get('auth/defaultGuard');
  }

  /**
   * Returns the log location (default admin/data/logs/)
   *
   * @return string The location
   */
  public function getLogLocation()
  {
    if (!$this->settings->exists('main/log_location')) {
      return str_replace(NIV, WEBSITE_ROOT, DATA_DIR) . 'logs' . DIRECTORY_SEPARATOR;
    }

    return $this->settings->get('main/log_location');
  }

  /**
   * Returns the maximun log file size
   *
   * @return int The maximun size in bytes
   */
  public function getLogfileMaxSize()
  {
    if (!$this->settings->exists('main/log_max_size')) {
      return Config::LOG_MAX_SIZE;
    }

    return $this->settings->get('main/log_max_size');
  }

  /**
   * Returns the admin name and email for logging
   *
   * @return array The name and email
   */
  public function getAdminAddress()
  {
    if (!$this->settings->exists('main/admin/email')) {
      /* Send to first user */
      $this->builder->select('users', 'nick,email')
	  ->getWhere()->bindInt('id', 1);
      $database = $this->builder->getResult();
      $a_data = $database->fetch_assoc();

      return array(
	  'name' => $a_data[0]['nick'],
	  'email' => $a_data[0]['email']
      );
    }

    return array(
	'name' => $this->settings->get('main/admin/name'),
	'email' => $this->settings->get('main/admin/email')
    );
  }

  /**
   * Returns if SSL is enabled
   *
   * @return int The SSL code
   * @see \youconix\core\services\Settings
   */
  public function isSslEnabled()
  {
    if (!$this->settings->exists('main/ssl')) {
      return \youconix\core\services\Settings::SSL_DISABLED;
    }

    return $this->settings->get('main/ssl');
  }

  public function isMobile()
  {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
  }

  /**
   * 
   * @return string
   */
  public function getCacheDirectory()
  {
    return $_SERVER['DOCUMENT_ROOT'] . DS . 'files' . DS . 'cache' . DS;
  }
  
  /**
   * 
   * @return string
   */
  public function getTimezone()
  {
    return 'Europe/Amsterdam';
  }
}
