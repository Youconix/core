<?php

namespace youconix\Core;

class IoC
{

  /**
   *
   * @var \Settings
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

    IoC::$rules['DAL'] = '\youconix\Core\ORM\Database\\' . $s_database;
    IoC::$rules['Builder'] = '\youconix\Core\ORM\Database\Builder_' . $s_database;
    IoC::$rules['DatabaseParser'] = '\youconix\Core\ORM\Database\Parser_' . $s_database;
  }

  protected function detectLogger()
  {
    if (!interface_exists('\Logger')) {
      require(NIV . 'Core/Interfaces/LoggerInterface.php');
    }

    if (defined('LOGGER')) {
      $type = LOGGER;
    } elseif (!$this->settings->exists('settings/main/logs')) {
      $type = 'default';
    } else {
      $type = $this->settings->get('main/logs');
    }

    switch ($type) {
      case 'default':
        IoC::$rules['Logger'] = '\youconix\Core\Services\Logger\LoggerDefault';
        break;

      case 'error_log':
        IoC::$rules['Logger'] = '\youconix\Core\Services\Logger\LoggerErrorLog';
        break;

      case 'sys_log':
        IoC::$rules['Logger'] = '\youconix\Core\Services\Logger\LoggerSysLog';
        break;

      default:
        IoC::$rules['Logger'] = $type;
    }
  }

  protected function detectLanguage()
  {
    if ($this->settings->exists('settings/language/type') && $this->settings->get('settings/language/type') == 'mo') {
      IoC::$rules['Language'] = '\youconix\Core\Services\Data\LanguageMO';
    } else {
      IoC::$rules['Language'] = '\youconix\Core\Services\Data\LanguageXML';
    }

    if (!function_exists('t')) {
      require(NIV . CORE . 'Services/Data/languageShortcut.php');
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
      if (file_exists(NIV . 'Includes/Classes/' . $s_item . '.php')) {
        IoC::$rules[$s_item] = '\Includes\Classes\\' . $s_item;
      } else {
        IoC::$rules[$s_item] = '\youconix\Core\Classes\\' . $s_item;
      }
    }

    IoC::$rules['Entities'] = '\youconix\Core\ORM\EntityHelper';
    IoC::$rules['Request'] = '\youconix\Core\Templating\Request';
    IoC::$rules['Cache'] = '\youconix\Core\Services\Cache';
    IoC::$rules['Config'] = IoC::$ruleConfig;
    IoC::$rules['Cookie'] = '\youconix\Core\Services\Cookie';
    IoC::$rules['FileHandler'] = IoC::$ruleFileHandler;
    IoC::$rules['Headers'] = '\youconix\Core\Services\Headers';
    IoC::$rules['Input'] = '\youconix\Core\Input';
    IoC::$rules['Output'] = '\youconix\Core\Templating\Template';
    IoC::$rules['Security'] = '\youconix\Core\Services\Security';
    IoC::$rules['Session'] = '\youconix\Core\Services\Session\Native';
    IoC::$rules['Settings'] = IoC::$ruleSettings;
    IoC::$rules['Validation'] = '\youconix\Core\Services\Validation';
    IoC::$rules['Layout'] = 'Includes\BaseLogicClass';
    IoC::$rules['EntityManager'] = '\youconix\Core\ORM\EntityManager';
  }

  public static function check($s_name)
  {
    if (substr($s_name, 0, 1) == '\\') {
      $s_name = substr($s_name, 1);
    }

    if ($s_name == 'Mailer') {
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
