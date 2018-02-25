<?php

interface ConfigInterface
{

  /**
   * Sets the current page and command.
   * Called from the Router
   * 
   * @param string $s_page
   * @param string $s_url
   * @param string $s_class
   * @param string $s_command
   */
  public function setCall($s_page, $s_url, $s_class, $s_command);

  /**
   * Returns the current controller class
   * 
   * @return string
   */
  public function getClass();

  /**
   * Returns the current page
   * 
   * @return string
   */
  public function getPage();
  
  /**
   * 
   * @return string
   */
  public function getUrl();

  /**
   * Returns the current command
   * 
   * @return string
   */
  public function getCommand();

  /**
   * Returns the default template directory
   * 
   * @return string
   */
  public function getTemplateDir();

  /**
   * Returns the settings service
   *
   * @return \SettingsInterface The service
   */
  public function getSettings();

  /**
   * Collects the installed languages
   *
   * @return array The installed languages
   */
  public function getLanguages();

  /**
   * Returns the shared template directory
   *
   * @return string template directory
   */
  public function getSharedStylesDir();

  /**
   * Returns the current language from the user
   *
   * @return string The language code
   */
  public function getLanguage();

  /**
   * Returns the used protocol
   *
   * @return string protocol
   */
  public function getProtocol();

  /**
   * Checks if the connection is via SSL/TSL
   *
   * @return bool True if the connection is encrypted
   */
  public function isSLL();

  /**
   * Checks if ajax-mode is active
   *
   * @return boolean if ajax-mode is active
   */
  public function isAjax();

  /**
   * Sets the framework in ajax-mode
   */
  public function setAjax();

  /**
   * Returns the server host
   *
   * @return string The host
   */
  public function getHost();

  /**
   * Returns the path to the website root
   * This value gets set in {LEVEL}
   *
   * @return string path
   */
  public function getBase();

  /**
   * Returns the login redirect url
   *
   * @return string The url
   */
  public function getLoginRedirect();

  /**
   * Returns the logout redirect url
   *
   * @return string The url
   */
  public function getLogoutRedirect();

  /**
   * Returns the registration redirect url
   *
   * @return string The url
   */
  public function getRegistrationRedirect();

  /**
   * Returns the authorisation guards
   *
   * @return array
   */
  public function getGuards();

  /**
   * Returns the default authorisation guard
   * 
   * @return string
   */
  public function getDefaultGuard();

  /**
   * Returns the log location (default admin/data/logs/)
   *
   * @return string The location
   */
  public function getLogLocation();

  /**
   * Returns the maximun log file size
   *
   * @return int The maximun size in bytes
   */
  public function getLogfileMaxSize();

  /**
   * Returns the admin name and email for logging
   *
   * @return array The name and email
   */
  public function getAdminAddress();

  /**
   * Returns if SSL is enabled
   *
   * @return int The SSL code
   * @see \youconix\core\services\Settings
   */
  public function isSslEnabled();

  public function isMobile();

  /**
   * @return boolean
   */
  public function getPrettyUrls();
  
  /**
   * @return array
   */
  public function getPasswordSettings() ;
  
  /**
   * 
   * @param int $level
   * @param int $minimumLength
   */
  public function setPasswordSettings($level, $minimumLength);
}
