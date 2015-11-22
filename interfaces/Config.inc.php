<?php
interface Config {
	/**
	 * Returns the settings service
	 *
	 * @return \Settings The service
	 */
	public function getSettings();
	
	/**
	 * Collects the installed languages
	 *
	 * @return array The installed languages
	 */
	public function getLanguages();
	
	/**
	 * Detects the template directory and layout
	 */
	public function detectTemplateDir();
	
	/**
	 * Returns the template directory
	 *
	 * @return String The template directory
	 */
	public function getTemplateDir();
	
	/**
	 * Returns the loaded template directory
	 *
	 * @return String template directory
	 */
	public function getStylesDir();
	
	public function getSharedStylesDir();
	
	/**
	 * Returns the main template layout
	 *
	 * @return String The layout
	 */
	public function getLayout();
	
	/**
	 * Returns the current language from the user
	 *
	 * @return string The language code
	 */
	public function getLanguage();
	
	/**
	 * Returns the used protocol
	 *
	 * @return String protocol
	 */
	public function getProtocol();
	
	/**
	 * Checks if the connection is via SSL/TSL
	 *
	 * @return bool True if the connection is encrypted
	 */
	public function isSLL();
	
	/**
	 * Returns the current page
	 *
	 * @return String page
	 */
	public function getPage();
	/**
	 * Sets the current page
	 *
	 * @param String $s_page
	 *            The new page
	 * @param String $s_command
	 *            The new command
	 *            @parma String $s_layout The new layout
	 */
	public function setPage($s_page, $s_command, $s_layout = 'default');
	
	/**
	 * Sets the layout
	 *
	 * @param string $s_layout
	 *            The layout
	 */
	public function setLayout($s_layout);
	
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
	 * Returns the request command
	 *
	 * @return String The command
	 */
	public function getCommand();
	
	/**
	 * Returns the server host
	 *
	 * @return String The host
	 */
	public function getHost();
	
	/**
	 * Returns the path to the website root
	 * This value gets set in {LEVEL}
	 *
	 * @return String path
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
	 * Returns if the normal login is activated
	 *
	 * @return boolean True if the normal login is activated
	 */
	public function isNormalLogin();
	
	public function isLDAPLogin();
	
	public function getLoginTypes();
	
	public function getOpenAuth();
	
	public function isOpenAuthEnabled($s_name);
	
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
	 * @see \core\services\Settings
	 */
	public function isSslEnabled();
	
	public function isMobile();
}