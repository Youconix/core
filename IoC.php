<?php
namespace youconix\core;

class IoC
{

    /**
     *
     * @var \Settings
     */
    protected $settings;

    public static $s_ruleSettings = '\youconix\core\services\Settings';

    public static $s_ruleFileHandler = '\youconix\core\services\FileHandler';

    public static $s_ruleConfig = '\youconix\core\Config';

    protected static $a_rules = array();
    
    public function load()
    {
        $this->settings = \Loader::inject(self::$s_ruleSettings);
        
        $this->detectDatabase();
        $this->detectLogger();
        $this->detectLanguage();
        $this->detectDefaults();
        
        $this->setRules();
    }

    protected function setRules()
    {}

    protected function detectDatabase()
    {
        $s_database = $this->settings->get('settings/SQL/type');
        
        IoC::$a_rules['DAL'] = '\youconix\core\ORM\database\\' . $s_database;
        IoC::$a_rules['Builder'] = '\youconix\core\ORM\database\Builder_' . $s_database;
        IoC::$a_rules['DatabaseParser'] =  '\youconix\core\ORM\database\Parser_'.$s_database;
    }

    protected function detectLogger()
    {
        if (! interface_exists('\Logger')) {
            require (NIV . 'core/interfaces/Logger.php');
        }
        
        if (defined('LOGGER')) {
            $s_type = LOGGER;
        }
        
        if (! $this->settings->exists('main/logs')) {
            $s_type = 'default';
        } else {
            $s_type = $this->settings->get('main/logs');
        }
        
        switch ($s_type) {
            case 'default':
                IoC::$a_rules['Logger'] = '\youconix\core\services\logger\LoggerDefault';
                break;
            
            case 'error_log':
                IoC::$a_rules['Logger'] = '\youconix\core\services\logger\LoggerErrorLog';
                break;
            
            case 'sys_log':
                IoC::$a_rules['Logger'] = '\youconix\core\services\logger\LoggerSysLog';
                break;
            
            default:
                IoC::$a_rules['Logger'] = $s_type;
        }
    }

    protected function detectLanguage()
    {
        if ($this->settings->exists('language/type') && $this->settings->get('language/type') == 'mo') {
            IoC::$a_rules['Language'] = '\youconix\core\services\data\LanguageMO';
        } else {
            IoC::$a_rules['Language'] = '\youconix\core\services\data\LanguageXML';
        }
        
        if (! function_exists('t')) {
            require (NIV . CORE . 'services/data/languageShortcut.php');
        }
    }

    protected function detectDefaults()
    {
        $a_items = array(
            'Header',
            'Footer',
            'Menu'
        );
        foreach ($a_items as $s_item) {
            if (file_exists(NIV . 'includes/classes/' . $s_item . '.php')) {
                IoC::$a_rules[$s_item] = '\includes\classes\\' . $s_item;
            } else {
                IoC::$a_rules[$s_item] = '\youconix\core\classes\\' . $s_item;
            }
        }
        
	IoC::$a_rules['Entities'] = '\youconix\core\ORM\EntityHelper';
        IoC::$a_rules['Request'] = '\youconix\core\templating\Request';
        IoC::$a_rules['Cache'] = '\youconix\core\services\Cache';
        IoC::$a_rules['Config'] = IoC::$s_ruleConfig;
        IoC::$a_rules['Cookie'] = '\youconix\core\services\Cookie';
        IoC::$a_rules['FileHandler'] = IoC::$s_ruleFileHandler;
        IoC::$a_rules['Headers'] = '\youconix\core\services\Headers';
        IoC::$a_rules['Input'] = '\youconix\core\Input';
        IoC::$a_rules['Output'] = '\youconix\core\templating\Template';
        IoC::$a_rules['Security'] = '\youconix\core\services\Security';
        IoC::$a_rules['Session'] = '\youconix\core\services\session\Native';
        IoC::$a_rules['Settings'] = IoC::$s_ruleSettings;
        IoC::$a_rules['Validation'] = '\youconix\core\services\Validation';
        IoC::$a_rules['Layout'] = 'includes\BaseLogicClass';
	IoC::$a_rules['EntityManager'] = '\youconix\core\ORM\EntityManager';
    }

    public static function check($s_name)
    {
        if (substr($s_name, 0, 1) == '\\') {
            $s_name = substr($s_name, 1);
        }
        
        if ($s_name == 'Mailer') {
            if (defined('DEBUG') || \youconix\core\Memory::isTesting()) {
                return '\youconix\core\mailer\PHPMailerDebug';
            } else {
                return '\youconix\core\mailer\PHPMailer';
            }
        }
        
        /* Check for interface */
        $s_interface = '';
        if (file_exists(WEB_ROOT .DS . 'core/interfaces/' . $s_name . '.php')) {
            $s_interface = WEB_ROOT .DS . 'core/interfaces/' . $s_name . '.php';
        } else 
            if (file_exists(WEB_ROOT .DS . 'includes/interfaces/' . $s_name . '.php')) {
                $s_interface = WEB_ROOT .DS . 'includes/interfaces/' . $s_name . '.php';
            }
        if (! empty($s_interface) && ! interface_exists('\\' . $s_name)) {
            require ($s_interface);
        }
        
        if (array_key_exists($s_name, IoC::$a_rules)) {
            return IoC::$a_rules[$s_name];
        }
        
        return null;
    }
}
