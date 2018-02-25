<?php

namespace youconix\Core;

class IoC
{

  /**
   *
   * @var \SettingsInterface
   */
  protected $settings;

  public static $ruleSettings = '\youconix\Core\Services\Settings';

  public static $ruleFileHandler = '\youconix\Core\Services\FileHandler';

  public static $ruleConfig = '\youconix\Core\Config';

  protected static $rules = [];

  public function load()
  {
    $this->detectDefaults();

    $this->settings = \Loader::inject(self::$ruleSettings);

    $this->detectDatabase();
    $this->detectLogger();
    $this->detectLanguage();
    $this->setRules();
  }

  protected function setRules()
  {
  }

  protected function detectDatabase()
  {
    $s_database = $this->settings->get('settings/SQL/type');

    IoC::$rules['DALInterface'] = '\youconix\Core\ORM\Database\\' . $s_database;
    IoC::$rules['BuilderInterface'] = '\youconix\Core\ORM\Database\Builder_' . $s_database;
    IoC::$rules['DatabaseParserInterface'] = '\youconix\Core\ORM\Database\Parser_' . $s_database;
  }

  protected function detectLogger()
  {
    if (!interface_exists('\LoggerInterface')) {
      require(NIV . 'Core/Interfaces/LoggerInterface.php');
    }

    if (defined('LoggerInterface')) {
      $type = LOGGER;
    } elseif (!$this->settings->exists('settings/main/logs')) {
      $type = 'default';
    } else {
      $type = $this->settings->get('settings/main/logs');
    }

    switch ($type) {
      case 'default':
        IoC::$rules['LoggerInterface'] = '\youconix\Core\Services\Logger\LoggerDefault';
        break;

      case 'error_log':
        IoC::$rules['LoggerInterface'] = '\youconix\Core\Services\Logger\LoggerErrorLog';
        break;

      case 'sys_log':
        IoC::$rules['LoggerInterface'] = '\youconix\Core\Services\Logger\LoggerSysLog';
        break;

      default:
        IoC::$rules['LoggerInterface'] = $type;
    }
  }

  protected function detectLanguage()
  {
    if ($this->settings->exists('settings/language/type') && $this->settings->get('settings/language/type') == 'mo') {
      IoC::$rules['LanguageInterface'] = '\youconix\Core\Services\Data\LanguageMO';
    } else {
      IoC::$rules['LanguageInterface'] = '\youconix\Core\Services\Data\LanguageXML';
    }

    if (!function_exists('t')) {
      require(NIV . CORE . 'Services/Data/languageShortcut.php');
    }
  }

  protected function detectDefaults()
  {
    $items = [
      'Header',
      'Footer',
      'Menu'
    ];
    foreach ($items as $item) {
      if (file_exists(NIV . 'Includes/Classes/' . $item . '.php')) {
        IoC::$rules[$item.'Interface'] = '\Includes\Classes\\' . $item;
      } else {
        IoC::$rules[$item.'Interface'] = '\youconix\Core\Classes\\' . $item;
      }
    }

    IoC::$rules['Entities'] = '\youconix\Core\ORM\EntityHelper';
    IoC::$rules['RequestInterface'] = '\youconix\Core\Templating\Request';
    IoC::$rules['CacheInterface'] = '\youconix\Core\Services\Cache';
    IoC::$rules['ConfigInterface'] = IoC::$ruleConfig;
    IoC::$rules['Cookie'] = '\youconix\Core\Services\Cookie';
    IoC::$rules['FileHandler'] = IoC::$ruleFileHandler;
    IoC::$rules['HeadersInterface'] = '\youconix\Core\Services\Headers';
    IoC::$rules['InputInterface'] = '\youconix\Core\Input';
    IoC::$rules['OutputInterface'] = '\youconix\Core\Templating\Template';
    IoC::$rules['SecurityInterface'] = '\youconix\Core\Services\Security';
    IoC::$rules['SessionInterface'] = '\youconix\Core\Services\Session\Native';
    IoC::$rules['SettingsInterface'] = IoC::$ruleSettings;
    IoC::$rules['ValidationInterface'] = '\youconix\Core\Services\Validation';
    IoC::$rules['Layout'] = 'Includes\BaseLogicClass';
    IoC::$rules['EntityManager'] = '\youconix\Core\ORM\EntityManager';
  }

  public static function check($s_name)
  {
    if (substr($s_name, 0, 1) == '\\') {
      $s_name = substr($s_name, 1);
    }

    if ($s_name == 'MailerInterface') {
      if (defined('DEBUG') || \youconix\core\Memory::isTesting()) {
        return '\youconix\Core\Mailer\PHPMailerDebug';
      } else {
        return '\youconix\Core\Mailer\PHPMailer';
      }
    }

    /* Check for interface */
    $s_interface = '';
    if (file_exists(WEB_ROOT . DS . 'Core/Interfaces/' . $s_name . '.php')) {
      $s_interface = WEB_ROOT . DS . 'Core/Interfaces/' . $s_name . '.php';
    } else
      if (file_exists(WEB_ROOT . DS . 'Includes/Unterfaces/' . $s_name . '.php')) {
        $s_interface = WEB_ROOT . DS . 'Includes/Interfaces/' . $s_name . '.php';
      }
    if (!empty($s_interface) && !interface_exists('\\' . $s_name)) {
      require($s_interface);
    }

    if (array_key_exists($s_name, IoC::$rules)) {
      return IoC::$rules[$s_name];
    }

    return null;
  }
}
