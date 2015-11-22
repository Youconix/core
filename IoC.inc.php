<?php
namespace  core;

class IoC {
	/**
	 * 
	 * @var \Settings
	 */
	private $settings;
	/**
	 * 
	 * @var \Config
	 */
	private $config;
	public static $s_ruleSettings = 'core\services\Settings';
	public static $s_ruleFileHandler = 'core\services\FileHandler';
	public static $s_ruleConfig = 'core\models\Config';
	protected static $a_rules = array();
	
	public function __construct(\Settings $settings){
		$this->settings = $settings;
		
		$this->detectDatabase();
		$this->detectLogger();
		$this->detectLanguage();
		$this->detectDefaults();
		
		$this->setRules();
	}
	
	protected function setRules(){
		
	}
	
	private function detectDatabase(){
		$s_database = $this->settings->get('settings/SQL/type');
		
		IoC::$a_rules['DAL'] = '\core\database\\'.$s_database;
		IoC::$a_rules['Builder'] = '\core\database\Builder_'.$s_database;
	}
	
	private function detectLogger(){
		if( !interface_exists('\Logger') ){
			require(NIV.'core/interfaces/Logger.inc.php');
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
				IoC::$a_rules['Logger'] = '\core\services\logger\LoggerDefault';
				break;
		
			case 'error_log':
				IoC::$a_rules['Logger'] = '\core\services\logger\LoggerErrorLog';
				break;
		
			case 'sys_log':
				IoC::$a_rules['Logger'] ='\core\services\logger\LoggerSysLog';
				break;
		
			default:
				IoC::$a_rules['Logger'] = $s_type;
		}
	}
	
	private function detectLanguage(){		
		if ($this->settings->exists('language/type') && $this->settings->get('language/type') == 'mo') {
			IoC::$a_rules['Language'] = 'core\services\data\LanguageMO';
		} else {
			IoC::$a_rules['Language'] = 'core\services\data\LanguageXML';
		}
		
		if (! function_exists('t')) {
			require (NIV.CORE.'services/data/languageShortcut.inc.php');
		}
	}
	
	private function detectDefaults(){
		$a_items = array('Header','Footer','Menu');
		foreach($a_items AS $s_item){
			if( file_exists(NIV.'includes/'.$s_item.'.inc.php') ){
				IoC::$a_rules[$s_item] = '\includes\classes\\'.$s_item;
			}
			else {
				IoC::$a_rules[$s_item] = '\core\classes\\'.$s_item;
			}
		}
		
		IoC::$a_rules['Request'] = '\core\templating\Request';
		IoC::$a_rules['Cache'] = '\core\services\Cache';
		IoC::$a_rules['Config'] = IoC::$s_ruleConfig;
		IoC::$a_rules['Cookie'] = '\core\services\Cookie';
		IoC::$a_rules['FileHandler'] = IoC::$s_ruleFileHandler;
		IoC::$a_rules['Headers'] = '\core\services\Headers';
		IoC::$a_rules['Input'] = '\core\Input';
		IoC::$a_rules['Output'] = '\core\templating\Template';
		IoC::$a_rules['Security'] = '\core\services\Security';
		IoC::$a_rules['Session'] = '\core\services\Session';
		IoC::$a_rules['Settings'] =  IoC::$s_ruleSettings;
		IoC::$a_rules['Validation'] = '\core\services\Validation';
	}
	
	public static function check($s_name){
		if( substr($s_name, 0,1) == '\\' ){
			$s_name = substr($s_name, 1);
		}
		
		if( $s_name == 'Mailer' ){
			if( defined('DEBUG') || \core\Memory::isTesting() ){
				return 'core\mailer\PHPMailerDebug';
			}
			else  {
				return 'core\mailer\PHPMailer';
			}
		}
		
		
		/* Check for interface */
		$s_interface = '';
		if( file_exists(NIV.'core/interfaces/'.$s_name.'.inc.php') ){
			$s_interface = NIV.'core/interfaces/'.$s_name.'.inc.php';
		}
		else if( file_exists(NIV.'includes/interfaces/'.$s_name.'.inc.php') ){
			$s_interface = NIV.'includes/interfaces/'.$s_name.'.inc.php';
		}
		if( !empty($s_interface) && !interface_exists('\\'.$s_name) ){
			require($s_interface);
		}
		
		if( array_key_exists($s_name, IoC::$a_rules) ){
			return IoC::$a_rules[$s_name];
		}
		
		return null;
	}
}