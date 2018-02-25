<?php

namespace youconix\core;

/**
 * Config contains the main runtime configuration of the framework.
 *
 * @since 2.0
 */
class Config implements \Config
{

    /** @var \youconix\core\services\FileHandler */
    protected $file;

    /** @var \Settings */
    protected $settings;

    /** @var \Cookie */
    protected $cookie;

    /** @var \Builder */
    protected $builder;

    /** @var string */
    protected $templateDir;

    /** @var string */
    protected $stylesDir;

    /** @var bool */
    protected $ajax = false;

    /** @var string */
    protected $base;

    /** @var string */
    protected $protocol;

    /** @var int */
    const LOG_MAX_SIZE = 10000000;

    /** @var string */
    protected $language;

    /** @var string */
    protected $page;

    /** @var string */
    protected $class;

    /** @var string */
    protected $url;

    /** @var string */
    protected $command;

    /** @var array */
    protected $observers = [];

    /**
     * PHP 5 constructor
     *
     * @param \youconix\core\services\FileHandler $file
     * @param \Settings $settings
     * @param \Cookie $cookie
     */
    public function __construct(\youconix\core\services\FileHandler $file,
                                \Settings $settings, \Cookie $cookie, \Builder $builder)
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
     * @param string $page
     * @param string $url
     * @param string $class
     * @param string $command
     */
    public function setCall($page, $url, $class, $command)
    {
        if (substr($page, 0, 1) == '/') {
            $page = substr($page, 1);
        }
        if (substr($page, -4) != '.php') {
            $page .= '.php';
        }
        if (substr($command, 0, 1) == '/') {
            $command = substr($command, 1);
        }

        $this->url = $url;
        $this->page = $page;
        $this->class = $class;
        $this->command = $command;

        $this->detectTemplateDir();
    }

    /**
     * Returns the current controller class
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Returns the current page
     *
     * @return string
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Returns the current command
     *
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Returns the default template directory
     *
     * @return string
     */
    public function getTemplateDir()
    {
        return $this->templateDir;
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
     * @param \SplObserver $observer
     * @see SplSubject::attach()
     */
    public function attach(\SplObserver $observer)
    {
        $this->observers->attach($observer);
    }

    /**
     * Removes the observer
     *
     * @param \SplObserver $observer
     * @see SplSubject::detach()
     */
    public function detach(\SplObserver $observer)
    {
        $this->observers->detach($observer);
    }

    /**
     * Notifies the observers
     *
     * @see SplSubject::notify()
     */
    public function notify()
    {
        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
    }

    /**
     * Loads the language
     */
    protected function loadLanguage()
    {
        /* Check language */
        $languages = $this->getLanguages();
        $this->language = $this->settings->get('settings/defaultLanguage');

        if (isset($_GET['lang'])) {
            if (in_array($_GET['lang'], $languages)) {
                $this->language = $_GET['lang'];
                $this->cookie->set('language', $this->language, '/');
            }
            unset($_GET['lang']);
        } elseif ($this->cookie->exists('language')) {
            if (in_array($this->cookie->get('language'), $languages)) {
                $this->language = $this->cookie->get('language');
                /* Renew cookie */
                $this->cookie->set('language', $this->language, '/');
            } else {
                $this->cookie->delete('language', '/');
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
        $languages = [];
        $languageFiles = $this->file->readDirectory(NIV . 'language');

        foreach ($languageFiles as $languageFile) {
            $languageFile = $languageFile->getFilename();
            if (strpos($languageFile, 'language_') !== false) {
                /* Fallback */
                return $this->getLanguagesOld();
            }

            if ($languageFile == '..' || $languageFile == '.' || strpos($languageFile,
                    '.') !== false) {
                continue;
            }

            $languages[] = $languageFile;
        }

        return $languages;
    }

    /**
     * Collects the installed languages
     * Old way of storing
     *
     * @return array The installed languages
     */
    protected function getLanguagesOld()
    {
        $languages = [];
        $languageFiles = $this->file->readDirectory(NIV . 'include/language');

        foreach ($languageFiles as $languageFile) {
            if (strpos($languageFile, 'language_') === false)
                continue;

            $languageFile = str_replace([
                'language_',
                '.lang'
            ], [
                '',
                ''
            ], $languageFile);

            $languages[] = $languageFile;
        }

        return $languages;
    }

    /**
     * Sets the default values
     *
     * @param \youconix\core\services\Settings $settings
     */
    protected function setDefaultValues(\youconix\core\services\Settings $settings)
    {
        if (!defined('DB_PREFIX')) {
            define('DB_PREFIX', $settings->get('settings/SQL/prefix'));
        }

        $base = $settings->get('settings/main/base');
        if (substr($base, 0, 1) != '/') {
            $this->base = '/' . $base;
        } else {
            $this->base = $base;
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
            define('WEBSITE_ROOT',
                $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $this->base);
        }
    }

    /**
     * Detects the template directory
     */
    protected function detectTemplateDir()
    {
        if (substr($this->getPage(), 0, 4) == 'admin') {
            $this->templateDir = $this->settings->get('settings/templates/admin_dir');
        } else if ($this->isMobile()) {
            $this->templateDir = $this->settings->get('settings/templates/mobile_dir');
        } else {
            $this->templateDir = $this->settings->get('settings/templates/default_dir');
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
                $headers = apache_request_headers();
                $this->ajax = (isset($headers['X-Requested-With']) && $headers['X-Requested-With'] == 'XMLHttpRequest');
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
        return (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : WEB_ROOT);
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
        $page = $this->getBase() . 'index/view';

        if ($this->settings->exists('settings/login/login')) {
            $page = $this->getBase() . $this->settings->get('settings/login/login');
        }

        return $page;
    }

    /**
     * Returns the logout redirect url
     *
     * @return string The url
     */
    public function getLogoutRedirect()
    {
        $page = $this->getBase() . 'index/view';

        if ($this->settings->exists('settings/login/logout')) {
            $page = $this->getBase() . $this->settings->get('settings/login/logout');
        }

        return $page;
    }

    /**
     * Returns the registration redirect url
     *
     * @return string The url
     */
    public function getRegistrationRedirect()
    {
        $page = $this->getBase() . 'index/view';

        if ($this->settings->exists('settings/login/registration')) {
            $page = $this->getBase() . $this->settings->get('settings/login/registration');
        }

        return $page;
    }

    /**
     * Returns the authorisation guards
     *
     * @return array
     */
    public function getGuards()
    {
        $guardsBlock = $this->settings->getBlock('settings/auth/guards');
        $guards = [];

        foreach ($guardsBlock AS $guardsRaw) {
            foreach ($guardsRaw AS $name => $guard) {
                $guards[$name] = $guard;
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
        return $this->settings->get('settings/auth/defaultGuard');
    }

    /**
     * Returns the log location (default admin/data/logs/)
     *
     * @return string The location
     */
    public function getLogLocation()
    {
        if (!$this->settings->exists('settings/main/log_location')) {
            return str_replace(NIV, WEBSITE_ROOT, DATA_DIR) . 'logs' . DIRECTORY_SEPARATOR;
        }

        return $this->settings->get('settings/main/log_location');
    }

    /**
     * Returns the maximun log file size
     *
     * @return int The maximun size in bytes
     */
    public function getLogfileMaxSize()
    {
        if (!$this->settings->exists('settings/main/log_max_size')) {
            return Config::LOG_MAX_SIZE;
        }

        return $this->settings->get('settings/main/log_max_size');
    }

    /**
     * Returns the admin name and email for logging
     *
     * @return array The name and email
     */
    public function getAdminAddress()
    {
        if (!$this->settings->exists('settings/main/admin/email')) {
            /* Send to first user */
            $this->builder->select('users', 'nick,email')
                ->getWhere()->bindInt('id', 1);
            $database = $this->builder->getResult();
            $data = $database->fetch_assoc();

            return array(
                'name' => $data[0]['nick'],
                'email' => $data[0]['email']
            );
        }

        return array(
            'name' => $this->settings->get('settings/main/admin/name'),
            'email' => $this->settings->get('settings/main/admin/email')
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
        if (!$this->settings->exists('settings/main/ssl')) {
            return \youconix\core\services\Settings::SSL_DISABLED;
        }

        return $this->settings->get('settings/main/ssl');
    }

    /**
     * @return bool
     */
    public function isMobile()
    {
        return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i",
            $_SERVER["HTTP_USER_AGENT"]);
    }

    /**
     *
     * @return string
     */
    public function getCacheDirectory()
    {
        return WEB_ROOT . DS . 'files' . DS . 'cache' . DS;
    }

    /**
     *
     * @return string
     */
    public function getTimezone()
    {
        return 'Europe/Amsterdam';
    }

    /**
     * @return boolean
     */
    public function getPrettyUrls()
    {
        if (!$this->settings->exists('settings/main/pretty_urls')) {
            return false;
        }

        return $this->settings->get('settings/main/pretty_urls');
    }

    /**
     * @return \stdClass
     */
    public function getPasswordSettings()
    {
        $level = 1;
        if ($this->settings->exists('settings/auth/password/level')) {
            $level = $this->settings->get('settings/auth/password/level');
        }

        $minimumLength = 8;
        if ($this->settings->exists('settings/auth/password/minimum_length')) {
            $minimumLength = $this->settings->get('settings/auth/password/minimum_length');
        }

        $settings = new \stdClass();
        $settings->level = $level;
        $settings->mimimunLength = $minimumLength;
        return $settings;
    }

    /**
     *
     * @param int $level
     * @param int $minimumLength
     */
    public function setPasswordSettings($level, $minimumLength)
    {
        $this->settings->set('settings/auth/password/level', $level);
        $this->settings->set('settings/auth/password/level', $minimumLength);
        $this->settings->save();
    }
}
