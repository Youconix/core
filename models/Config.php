<?php
namespace core\models;

/**
 * Miniature-happiness is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Miniature-happiness is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Miniature-happiness. If not, see <http://www.gnu.org/licenses/>.
 *
 * Config contains the main runtime configuration of the framework.
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 2.0
 */
class Config extends Model implements \Config,\SplSubject
{
	/**
	 * @var \core\services\FileHandler
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

    protected $s_templateDir;

    protected $bo_ajax = false;

    protected $s_base;

    protected $s_page;

    protected $s_protocol;

    protected $s_command = 'view';

    protected $s_layout = 'default';

    protected $a_observers;

    const LOG_MAX_SIZE = 10000000;

    protected $s_language;

    /**
     * PHP 5 constructor
     *       
     * @param \core\services\FileHandler $file
     * @param \Settings $settings            
     * @param \Cookie $cookie            
     */
    public function __construct(\core\services\FileHandler $file,\Settings $settings, \Cookie $cookie, \Builder $builder)
    {
    	$this->file = $file;
        $this->settings = $settings;
        $this->cookie = $cookie;
        $this->builder = $builder;
        
        $this->a_observers = new \SplObjectStorage();
        
        $this->loadLanguage();
        
        $this->setDefaultValues($settings);
        
        $this->detectTemplateDir();
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
        $this->_observers->detach($observer);
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
        $this->s_language = $this->settings->get('defaultLanguage');
        
        if (isset($_GET['lang'])) {
            if (in_array($_GET['lang'], $a_languages)) {
                $this->s_language = $_GET['lang'];
                $this->cookie->set('language', $this->s_language, '/');
            }
            unset($_GET['lang']);
        } else {
            if ($this->cookie->exists('language')) {
                if (in_array($this->cookie->get('language'), $a_languages)) {
                    $this->s_language = $this->cookie->get('language');
                    /* Renew cookie */
                    $this->cookie->set('language', $this->s_language, '/');
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
        if (! defined('DB_PREFIX')) {
            define('DB_PREFIX', $settings->get('settings/SQL/prefix'));
        }
        
        $s_base = $settings->get('settings/main/base');
        if (substr($s_base, 0, 1) != '/') {
            $this->s_base = '/' . $s_base;
        } else {
            $this->s_base = $s_base;
        }
        
        if (! defined('BASE')) {
            define('BASE', NIV);
        }
        
        /* Get page */
        $s_page = $_SERVER['SCRIPT_NAME'];
        while (substr($s_page, 0, 1) == '/') {
            $s_page = substr($s_page, 1);
        }
        
        if ($s_base != '/') {
            if (stripos($s_page, $s_base) !== false) {
                $s_page = substr($s_page, strlen($s_base));
            }
        }
        
        while (substr($s_page, 0, 1) == '/') {
            $s_page = substr($s_page, 1);
        }
        $this->s_page = $s_page;
        
        /* Get protocol */
        $this->s_protocol = ((! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
        
        $this->detectAjax();
        
        if (! defined('LEVEL')) {
            define('LEVEL', '/');
        }
        
        $this->s_command = 'index';
        if (isset($_GET['command'])) {
            $this->s_command = $_GET['command'];
        } else 
            if (isset($_POST['command'])) {
                $this->s_command = $_POST['command'];
            }
        if (! defined('WEBSITE_ROOT')) {
            define('WEBSITE_ROOT', $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $this->s_base);
        }
    }

    /**
     * Detects an AJAX call
     */
    protected function detectAjax()
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
            $this->bo_ajax = true;
        } else 
            if (function_exists('apache_request_headers')) {
                $a_headers = apache_request_headers();
                $this->bo_ajax = (isset($a_headers['X-Requested-With']) && $a_headers['X-Requested-With'] == 'XMLHttpRequest');
            }
        if (! $this->bo_ajax && ((isset($_GET['AJAX']) && $_GET['AJAX'] == 'true') || (isset($_POST['AJAX']) && $_POST['AJAX'] == 'true'))) {
            $this->bo_ajax = true;
        }
    }
    
    /**
     * Detects the template directory and layout
     */
    public function detectTemplateDir(){
    	$s_uri = $this->s_page;//$_SERVER['REQUEST_URI'];
    	while( strpos($s_uri,'//') !== false ){
    		$s_uri = str_replace('//','/',$s_uri);
    	}
        if (preg_match('#^/?admin/#', $s_uri)) {
            $this->loadAdminTemplateDir();
        } else {
            $this->loadTemplateDir();
        }
        
        $this->notify();
    }

    /**
     * Loads the template directory
     */
    protected function loadAdminTemplateDir()
    {
        $this->s_templateDir = $this->settings->get('settings/templates/admin_dir');
        $this->s_layout = $this->settings->get('settings/templates/admin_layout');
    }

    /**
     * Loads the template directory
     */
    protected function loadTemplateDir()
    {
        if ($this->isMobile()) {
            $s_templateDir = $this->settings->get('settings/templates/mobile_dir');
            $this->s_layout = $this->settings->get('settings/templates/mobile_layout');
        } else {
            $s_templateDir = $this->settings->get('settings/templates/default_dir');
            $this->s_layout = $this->settings->get('settings/templates/default_layout');
        }
        
        if (isset($_GET['protected_style_dir'])) {
            $s_styleDir = $this->clearLocation($_GET['protected_style_dir']);
            if ($this->file->exists(NIV . 'styles/' . $s_styleDir . '/templates/layouts')) {
                $s_templateDir = $s_styleDir;
                $this->cookie->set('protected_style_dir', $s_templateDir, '/');
            } else 
                if ($this->cookie->exists('protected_style_dir')) {
                    $this->cookie->delete('protected_style_dir', '/');
                }
        } else 
            if ($this->cookie->exists('protected_style_dir')) {
                $s_styleDir = $this->clearLocation($this->cookie->get('protected_style_dir'));
                if ($this->file->exists(NIV . 'styles/' . $s_styleDir . '/templates/layouts')) {
                    $s_templateDir = $s_styleDir;
                    $this->cookie->set('protected_style_dir', $s_templateDir, '/');
                } else {
                    $this->cookie->delete('protected_style_dir', '/');
                }
            }
        $this->s_templateDir = $s_templateDir;
        
        if (defined('LAYOUT')) {
            $this->s_layout = LAYOUT;
        }
    }

    /**
     * Clears the location path from evil input
     *
     * @param String $s_location            
     * @return String path
     */
    protected function clearLocation($s_location)
    {
        while ((strpos($s_location, './') !== false) || (strpos($s_location, '../') !== false)) {
            $s_location = str_replace(array(
                './',
                '../'
            ), array(
                '',
                ''
            ), $s_location);
        }
        
        return $s_location;
    }

    /**
     * Returns the template directory
     *
     * @return String The template directory
     */
    public function getTemplateDir()
    {
        return $this->s_templateDir;
    }

    /**
     * Returns the loaded template directory
     *
     * @return String template directory
     */
    public function getStylesDir()
    {
        return 'styles/' . $this->s_templateDir . '/';
    }
    
    public function getSharedStylesDir(){
        return 'styles/shared/';
    }

    /**
     * Returns the main template layout
     *
     * @return String The layout
     */
    public function getLayout()
    {
        return $this->s_layout;
    }

    /**
     * Returns the current language from the user
     *
     * @return string The language code
     */
    public function getLanguage()
    {
        return $this->s_language;
    }

    /**
     * Returns the used protocol
     *
     * @return String protocol
     */
    public function getProtocol()
    {
        return $this->s_protocol;
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
     * Returns the current page
     *
     * @return String page
     */
    public function getPage()
    {
        return $this->s_page;
    }

    /**
     * Sets the current page
     *
     * @param String $s_page
     *            The new page
     * @param String $s_command
     *            The new command
     *            @parma String $s_layout The new layout
     */
    public function setPage($s_page, $s_command, $s_layout = 'default')
    {
        $this->s_page = $s_page;
        $this->s_command = $s_command;
        $this->s_layout = $s_layout;
        
        $this->detectTemplateDir();
    }

    /**
     * Sets the layout
     *
     * @param string $s_layout
     *            The layout
     */
    public function setLayout($s_layout)
    {
        $this->s_layout = $s_layout;
        
        $this->notify();
    }

    /**
     * Checks if ajax-mode is active
     *
     * @return boolean if ajax-mode is active
     */
    public function isAjax()
    {
        return $this->bo_ajax;
    }

    /**
     * Sets the framework in ajax-mode
     */
    public function setAjax()
    {
        $this->bo_ajax = true;
    }

    /**
     * Returns the request command
     *
     * @return String The command
     */
    public function getCommand()
    {
        return $this->s_command;
    }

    /**
     * Returns the server host
     *
     * @return String The host
     */
    public function getHost()
    {
        return $_SERVER['HTTP_HOST'];
    }

    /**
     * Returns the path to the website root
     * This value gets set in {LEVEL}
     *
     * @return String path
     */
    public function getBase()
    {
        return $this->s_base;
    }

    /**
     * Returns the login redirect url
     *
     * @return string The url
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
     * Returns if the normal login is activated
     *
     * @return boolean True if the normal login is activated
     */
    public function isNormalLogin()
    {
        if (! $this->settings->exists('login/normalLogin') || $this->settings->get('login/normalLogin') != 1) {
            return false;
        }
        return true;
    }
    
    public function isLDAPLogin(){
        if (! $this->settings->exists('login/LDAP') || $this->settings->get('login/LDAP/status') != 1) {
            return false;
        }
        return true;
    }
    
    public function getLoginTypes(){
        $a_login = array();
        if( $this->isNormalLogin() ){
            $a_login[] = 'normal';
        }
        if( $this->isLDAPLogin() ){
            $a_login[] = 'ldap';
        }
        $a_login = array_merge($a_login,$this->getOpenAuth());
        return $a_login;
    }
    
    public function getOpenAuth(){
        if( !$this->settings->exists('login/openAuth') ){
            return array();
        }
        
        $a_types = $this->settings->getBlock('login/openAuth/type');
        $a_openAuth = array();
        foreach( $a_types AS $type ){
            if( $type->tagName != 'type' ){ continue; }
            
            if( $this->settings->get('login/openAuth/'.$type->nodeValue.'/status') == 1 ){
                $a_openAuth[] = $type->nodeValue;
            }
        }
        
        return $a_openAuth;
    }
    
    public function isOpenAuthEnabled($s_name){
        if( !$this->settings->exists('login/openAuth') || !$this->settings->exists('login/openAuth/'.$s_name)){
            return false;
        }
        
        return ($this->settings->exists('login/openAuth/'.$s_name.'/status') == 1 );
    }

    /**
     * Returns the log location (default admin/data/logs/)
     *
     * @return string The location
     */
    public function getLogLocation()
    {
        if (! $this->settings->exists('main/log_location')) {
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
        if (! $this->settings->exists('main/log_max_size')) {
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
        if (! $this->settings->exists('main/admin/email')) {
            /* Send to first user */
            $this->builder->select('users', 'nick,email')
                ->getWhere()
                ->addAnd('id', 'i', 1);
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
     * @see \core\services\Settings
     */
    public function isSslEnabled()
    {
        if (! $this->settings->exists('main/ssl')) {
            return \core\services\Settings::SSL_DISABLED;
        }
        
        return $this->settings->get('main/ssl');
    }

    public function isMobile()
    {
        return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
    }
}
