<?php

namespace youconix\Core;

if (!class_exists('\CoreException')) {
  require(WEB_ROOT . DIRECTORY_SEPARATOR . CORE . 'Exceptions/CoreException.php');
}

/**
 * Memory-handler for controlling memory and auto starting the framework
 * @since 1.0
 */
final class Memory
{

  private static $testing = false;
  private static $service;
  private static $serviceData;
  private static $model;
  private static $modelData;
  private static $helper;
  private static $helperData;
  private static $class;
  private static $interface;
  private static $database;
  private static $prettyUrls;
  private static $cache;

  /**
   * Destructor
   */
  public function __destruct()
  {
    self::reset();
  }

  /**
   * Starts the framework in testing mode.
   * DO NOT USE THIS IN PRODUCTION
   */
  public static function setTesting()
  {
    self::$testing = true;
    if (!defined('DEBUG')) {
      define('DEBUG', 'true');
    }

    if (!defined('PROCESS')) {
      define('PROCESS', 1);
    }

    self::startUp();
  }

  /**
   * Starts the framework
   */
  public static function startUp()
  {
    if (!is_null(self::$cache)) {
      return;
    }

    if (!defined('DS')) {
      define('DS', DIRECTORY_SEPARATOR);
    }

    try {
      if (!defined('DATA_DIR')) {
        if (self::$testing) {
          define('DATA_DIR', NIV . 'admin' . DS . 'data' . DS . 'tests' . DS);
        } else {
          define('DATA_DIR', NIV . 'admin' . DS . 'data' . DS);
        }
      }

      /* Prepare cache */
      self::$cache = [];

      self::$service = [
        'systemPath' => NIV . CORE . 'Services' . DS,
        'userPath' => NIV . 'Includes' . DS . 'Services' . DS,
        'systemNamespace' => '\youconix\Core\Services\\',
        'userNamespace' => '\Includes\Services\\'
      ];
      self::$serviceData = [
        'systemPath' => NIV . CORE . 'Services' . DS . 'Data' . DS,
        'userPath' => NIV . 'Includes' . DS . 'Services' . DS . 'Data' . DS,
        'systemNamespace' => '\youconix\Core\Services\Data\\',
        'userNamespace' => '\Includes\Services\Data\\'
      ];
      self::$model = array(
        'systemPath' => NIV . CORE . 'Repositories' . DS,
        'userPath' => NIV . 'Includes' . DS . 'Repositories' . DS,
        'systemNamespace' => '\youconix\Core\Repositories\\',
        'userNamespace' => '\Includes\Repositories\\'
      );
      self::$modelData = array(
        'systemPath' => NIV . CORE . 'Models' . DS . 'entities' . DS,
        'userPath' => NIV . 'Includes' . DS . 'Entities',
        'systemNamespace' => '\youconix\Core\Entities\\',
        'userNamespace' => '\Includes\Entities\\'
      );
      self::$helper = array(
        'systemPath' => NIV . CORE . 'Helpers' . DS,
        'userPath' => NIV . 'Includes' . DS . 'Helpers' . DS,
        'systemNamespace' => '\youconix\Core\Helpers\\',
        'userNamespace' => '\Includes\Helpers\\'
      );
      self::$helperData = array(
        'systemPath' => NIV . CORE . 'Helpers' . DS . 'Data' . DS,
        'userPath' => NIV . 'Includes' . DS . 'Helpers' . DS . 'Data' . DS,
        'systemNamespace' => '\youconix\Core\Helpers\Data\\',
        'userNamespace' => '\Includes\Helpers\Data\\'
      );
      self::$class = array(
        'systemPath' => NIV . CORE . 'Classes' . DS,
        'userPath' => NIV . 'Includes' . DS . 'Classes' . DS,
        'systemNamespace' => '\youconix\Core\Classes\\',
        'userNamespace' => '\Includes\Classes\\'
      );
      self::$interface = array(
        'systemPath' => NIV . CORE . 'Interfaces' . DS,
        'userPath' => NIV . 'Includes' . DS . 'Interfaces' . DS,
        'systemNamespace' => '\youconix\Core\Interfaces\\',
        'userNamespace' => '\Includes\Interfaces\\'
      );
      self::$database = array(
        'systemPath' => NIV . CORE . 'ORM' . DS,
        'userPath' => NIV . 'Includes' . DS . 'ORM' . DS,
        'systemNamespace' => '\youconix\Core\ORM\\',
        'userNamespace' => '\Includes\ORM\\'
      );

      require_once(NIV . CORE . 'AbstractObject.php');
      require_once(NIV . CORE . 'Services' . DS . 'AbstractService.php');

      /* Load IoC */
      self::$cache['IoC'] = self::loadCoreClass('\youconix\Core\IoC');

      /* Load File Handler */
      self::$cache[IoC::$ruleFileHandler] = self::loadCoreClass(IoC::$ruleFileHandler);

      /* Load autoloader */
      require(NIV . CORE . 'Loader.php');

      self::$cache['IoC']->load();

      /* Load standard Services */
      $file = self::$cache[IoC::$ruleFileHandler];
      if (!$file->exists(NIV . 'files' . DS . 'updates')) {
        $file->newDirectory(NIV . 'files' . DS . 'updates', 0700);
      }

      $caller = IoC::$ruleSettings;
      $settings = \Loader::inject($caller);
      self::$cache[$caller] = $settings;
      self::$cache['\SettingsInterface'] = $settings;
      unset($caller);
      date_default_timezone_set($settings->get('settings/main/timeZone'));

      self::setDefaultValues($settings);

      $entityHelper = \Loader::inject('\youconix\Core\ORM\EntityHelper');
      $entityHelper->buildMap();
      $entityHelper->buildProxies();
      self::$cache['\youconix\Core\ORM\EntityHelper'] = $entityHelper;
    } catch (\Exception $e) {
      throw new \CoreException('Starting up framework failed', 0, $e);
    }
  }

  /**
   * @param string $className
   * @return mixed
   */
  private static function loadCoreClass($className)
  {
    $fileName = str_replace('\\', DS, str_replace('\youconix\Core\\', '', $className));

    require(NIV . CORE . $fileName . '.php');
    if (file_exists(NIV . 'Includes' . DS . $fileName . '.php')) {
      require(NIV . 'Includes' . DS . $fileName . '.php');
      $className = str_replace('youconix\Core', 'Includes', $className);
    }
    $object = new $className();
    return $object;
  }

  /**
   * Sets the default values
   *
   * @param \SettingsInterface $settings
   */
  private static function setDefaultValues(\SettingsInterface $settings)
  {
    if (isset($_SERVER['SERVER_ADDR']) && in_array($_SERVER['SERVER_ADDR'], [
        '127.0.0.1',
        '::1'
      ]) && !defined('DEBUG')) {
      define('DEBUG', null);
    }

    if (defined('DEBUG')) {
      ini_set('display_errors', 'on');
    } else {
      ini_set('display_errors', 'off');
    }

    error_reporting(E_ALL);

    self::$prettyUrls = false;
    if ($settings->exists('settings/main/pretty_urls') && $settings->get('settings/main/pretty_urls') == 1) {
      self::$prettyUrls = true;
    }
  }

  /**
   * Checks if the class is in the cache
   *
   * @param string $name
   * @return boolean
   */
  public static function IsInCache($name)
  {
    return array_key_exists($name, self::$cache);
  }

  /**
   * Sets the object in the cache
   *
   * @param string $name
   * @param Object $object
   */
  public static function setCache($name, $object)
  {
    self::$cache[$name] = $object;
  }

  /**
   * Returns the object from the cache
   *
   * @param string $name
   * @return Object The object or null
   */
  public static function getCache($name)
  {
    if (!self::IsInCache($name)) {
      return null;
    }

    return self::$cache[$name];
  }

  /**
   * Returns the used protocol
   *
   * @return string protocol
   * @deprecated since version 2.
   * @see \ConfigInterface:getProtocol
   */
  public static function getProtocol()
  {
    if (!self::isTesting()) {
      trigger_error("This function has been deprecated in favour of Core/models/Config->getProtocol().", E_USER_DEPRECATED);
    }
    return \Loader::inject('\ConfigInterface')->getProtocol();
  }

  /**
   * Returns the current page
   *
   * @return string page
   * @deprecated since version 2.
   * @see \ConfigInterface:getPage
   */
  public static function getPage()
  {
    if (!self::isTesting()) {
      trigger_error("This function has been deprecated in favour of Core/models/Config->getPage().", E_USER_DEPRECATED);
    }
    return \Loader::inject('\ConfigInterface')->getPage();
  }

  /**
   * Checks if ajax-mode is active
   *
   * @return boolean if ajax-mode is active
   * @deprecated since version 2.
   * @see \ConfigInterface:isAjax
   */
  public static function isAjax()
  {
    if (!self::isTesting()) {
      trigger_error("This function has been deprecated in favour of Core/models/Config->isAjax().", E_USER_DEPRECATED);
    }
    return \Loader::inject('\ConfigInterface')->isAjax();
  }

  /**
   * Sets the framework in ajax-
   *
   * @deprecated since version 2
   * @see \ConfigInterface::setAjax()
   */
  public static function setAjax()
  {
    if (!self::isTesting()) {
      trigger_error("This function has been deprecated in favour of Core/models/Config->setAjax().", E_USER_DEPRECATED);
    }
    return \Loader::inject('\ConfigInterface')->setAjax();
  }

  /**
   * Checks if testing-mode is active
   *
   * return boolean True if testing-mode is active
   */
  public static function isTesting()
  {
    return self::$testing;
  }

  /**
   * Returns the base directory
   *
   * @return string directory
   * @deprecated since version 2.
   * @See \Config:getBase
   */
  public static function getBase()
  {
    if (!self::isTesting()) {
      trigger_error("This function has been deprecated in favour of Core/models/Config->getBase().", E_USER_DEPRECATED);
    }
    return \Loader::inject('\ConfigInterface')->getBase();
  }

  /**
   * Ensures that the given class is loaded
   *
   * @param string $class
   * @deprecated
   *
   * @throws RuntimeException the class does not exists in include/class/
   */
  public static function ensureClass($class)
  {
    if (!self::isTesting()) {
      trigger_error("This function has been deprecated.", E_USER_DEPRECATED);
    }

    if (!class_exists($class)) {
      $file = self::$cache[IoC::$ruleFileHandler];
      if (!$file->exists(self::$class['systemPath'] . $class . '.inc.php')) {
        throw new \RuntimeException('Can not find class ' . $class);
      }

      require_once(self::$class['systemPath'] . $class . '.inc.php');
    }
  }

  /**
   * Ensures that the given interface is loaded
   *
   * @param string $interface
   * @deprecated
   *
   * @throws RuntimeException the interface does not exists in include/interface/
   */
  public static function ensureInterface($interface)
  {
    if (!self::isTesting()) {
      trigger_error("This function has been deprecated.", E_USER_DEPRECATED);
    }
    if (!interface_exists($interface)) {
      $file = self::$cache[IoC::$ruleFileHandler];
      if (!$file->exists(self::$interface['systemPath'] . $interface . '.inc.php')) {
        throw new \RuntimeException('Can not find interface ' . $interface);
      }

      require_once(self::$interface['systemPath'] . $interface . '.inc.php');
    }
  }

  /**
   * Checks if a file is a Core module
   *
   * @param string $name
   * @param array $memoryItems
   * @param string $path
   * @param string $namespace
   * @param string $fallback
   * @deprecated
   *
   * @return boolean
   */
  private static function isModule($name, array $memoryItems, $path, $namespace, $fallback = '')
  {
    if (!self::isTesting()) {
      trigger_error("This function has been deprecated.", E_USER_DEPRECATED);
    }
    $name = ucfirst($name);

    /* Call class file */
    $file = self::$cache[IoC::$ruleFileHandler];
    $path = $path . '/' . $name . '.php';

    if (!$file->exists($path)) {
      return false;
    }

    $item = $file->readFile($path);

    if ((strpos($item, 'namespace ' . $namespace . ';') !== false) || (strpos($item, 'namespace ' . $namespace . '\Data;') !== false)) {
      return true;
    }

    if (!empty($fallback) && (strpos($item, 'class ' . $fallback . '_' . $name) !== false)) {
      return true;
    }

    return false;
  }

  /**
   * Loads the requested module
   * Automatically overrides the system module with the user defined one
   *
   * @param string $name
   * @param string $memoryType
   * @param array $data
   * @param string $fallback
   * @return Object The module
   * @deprecated
   *
   * @throws \RuntimeException If the requested module does not exist
   * @throws \OverrideException If the override module is not a child of the system module
   */
  private static function loadModule($name, $memoryType, array $data, $fallback = '')
  {
    if (!self::isTesting()) {
      trigger_error("This function has been deprecated.", E_USER_DEPRECATED);
    }
    $name = ucfirst($name);

    $object = \Loader::inject($data['systemNamespace'] . $name);

    if (is_null($object)) {
      if (!empty($fallback) && class_exists($fallback . '_' . $name)) {
        $caller = $fallback . '_' . $name;
        $object = new $caller();
      } else {
        throw new \RuntimeException('Can not find ' . $memoryType . ' ' . $name);
      }
    }

    return $object;
  }

  /**
   * API for checking or a helper exists
   *
   * @param string $name
   * @param bool $data
   * @deprecated
   *
   * @return bool if the helper exists, otherwise false
   */
  public static function isHelper($name, $data = false)
  {
    if (!self::isTesting()) {
      trigger_error("This function has been deprecated.", E_USER_DEPRECATED);
    }
    $memoryItems = [
      'helper',
      'helper-data'
    ];
    $fallback = 'Helper';

    if ($data) {
      $path = self::$helper['systemPath'] . 'data/';
      $namespace = 'Core\helpers\data';
    } else {
      $path = self::$helper['systemPath'];
      $namespace = 'Core\helpers';
    }

    return self::isModule($name, $memoryItems, $path, $namespace, $fallback);
  }

  /**
   * Loads the requested helper
   *
   * @param string $name
   * @param bool $data
   * @return Helper The requested helper
   * @deprecated
   *
   * @throws Exception If the requested helper does not exist
   * @throws \OverrideException If the override module is not a child of the system module
   */
  public static function helpers($name, $data = false)
  {
    if (!self::isTesting()) {
      trigger_error("This function has been deprecated.", E_USER_DEPRECATED);
    }
    if ($data) {
      return self::loadModule($name, 'helper-data', self::$helperData);
    }

    $data = self::$helper;
    if ($name == 'HTML') {
      $a_data['systemNameSpace'] = '\youconix\Core\Helpers\Html\\';
    }

    return self::loadModule($name, 'helper', $data, 'Helper');
  }

  /**
   * API for checking or a service exists
   *
   * @param string $name
   * @param bool $data
   * @deprecated
   *
   * @return bool if the service exists, otherwise false
   */
  public static function isService($name, $data = false)
  {
    if (!self::isTesting()) {
      trigger_error("This function has been deprecated, and will be removed after version 3.", E_USER_DEPRECATED);
    }
    $memoryItems = [
      'service',
      'service-data'
    ];
    if ($data) {
      $path = self::$service['systemPath'] . 'data/';
      $namespace = 'Core\services\data';
    } else {
      $path = self::$service['systemPath'];
      $namespace = 'Core\services';
    }
    $fallback = 'Service';

    return self::isModule($name, $memoryItems, $path, $namespace, $fallback);
  }

  /**
   * Loads the requested service
   *
   * @param string $name
   * @param bool $data
   * @return Service The requested service
   * @deprecated
   *
   * @throws Exception If the requested service does not exist
   * @throws \OverrideException If the override module is not a child of the system module
   */
  public static function services($name, $data = false)
  {
    if (!self::isTesting()) {
      trigger_error("This function has been deprecated in favour of \Loader::inject().", E_USER_DEPRECATED);
    }
    if ($data) {
      return self::loadModule($name, 'service', self::$serviceData);
    }

    return self::loadModule($name, 'service', self::$service, 'Service');
  }

  /**
   * API for checking or a model exists
   *
   * @param string $name
   * @param bool $data
   * @deprecated
   *
   * @return bool if the model exists, otherwise false
   */
  public static function isModel($name, $data = false)
  {
    if (!self::isTesting()) {
      trigger_error("This function has been deprecated, and will be removed following version 3.", E_USER_DEPRECATED);
    }
    $memoryItems = [
      'model',
      'model-data'
    ];
    if ($data) {
      $path = self::$model['systemPath'] . 'data/';
      $namespace = 'Core\models\data';
    } else {
      $path = self::$model['systemPath'];
      $namespace = 'Core\models';
    }
    $fallback = 'Model';

    return self::isModule($name, $memoryItems, $path, $namespace, $fallback);
  }

  /**
   * Loads the requested model
   *
   * @param string $name
   * @param bool $data
   * @return Model The requested model
   * @deprecated
   *
   * @throws Exception If the requested model does not exist
   * @throws \OverrideException If the override module is not a child of the system module
   */
  public static function models($name, $data = false)
  {
    if (!self::isTesting()) {
      trigger_error("This function has been deprecated in favour of \Loader:inject().", E_USER_DEPRECATED);
    }
    if ($data) {
      return self::loadModule($name, 'model-data', self::$modelData);
    }

    return self::loadModule($name, 'model', self::$model);
  }

  /**
   * Checks if a helper, service or model is loaded
   *
   * @param string $type
   * @param string $name
   * @deprecated
   *
   * @return boolean if the value exists in the memory, false if it does not
   */
  public static function isLoaded($type, $name)
  {
    if (!self::isTesting()) {
      trigger_error("This function has been deprecated.", E_USER_DEPRECATED);
    }
    $type = strtolower($type);
    $name = ucfirst($name);

    if (array_key_exists('\youconix\core\\' . $type . 's\\' . $name, self::$cache)) {
      return true;
    }

    return false;
  }

  /**
   * Loads a class
   *
   * @param string $class
   * @return AbstractObject
   * @throws \RuntimeException If the class does not exist
   * @deprecated
   *
   * @throws \OverrideException the override class is not a child of the default class
   */
  public static function loadClass($class)
  {
    if (!self::isTesting()) {
      trigger_error("This function has been deprecated.", E_USER_DEPRECATED);
    }
    $class = ucfirst($class);

    /* Check model */
    $file = self::$cache['\youconix\core\services\File'];
    $override = false;
    $path = '';
    $caller = '';
    $callerParent = '';

    if ($file->exists(self::$class['userPath'] . $class . '.php')) {
      if ($file->exists(self::$class['systemPath'] . $class . '.php')) {
        $bo_override = true;
        $s_callerParent = self::$class['systemNamespace'] . $class;

        if (!class_exists($s_callerParent)) {
          require(self::$class['systemPath'] . $class . '.php');
        }
      }

      require_once(self::$class['userPath'] . $class . '.php');
      $path = self::$class['userPath'] . $class . '.php';
      $caller = self::$class['userNamespace'] . $class;
    } else
      if ($file->exists(self::$class['systemPath'] . $class . '.php')) {
        require_once(self::$class['systemPath'] . $class . '.php');
        $path = self::$class['systemPath'] . $class . '.php';
        $caller = self::$class['systemNamespace'] . $class;
      } else {
        throw new \RuntimeException('Can not find class ' . $class);
      }

    $object = \Loader::inject($caller);

    if (!empty($callerParent) && !($object instanceof $callerParent)) {
      throw new \OverrideException('Override ' . $caller . ' is not a child of ' . $callerParent . '.');
    }
    return $object;
  }

  /**
   * Loads an interface
   *
   * @param string $interface
   * @deprecated
   *
   * @throws \RuntimeException If the interface does not exist
   */
  public static function loadInterface($interface)
  {
    if (!self::isTesting()) {
      trigger_error("This function has been deprecated.", E_USER_DEPRECATED);
    }
    $interface = ucfirst($interface);

    /* Check model */
    $file = self::$cache[IoC::$ruleFileHandler];

    if ($file->exists(self::$interface['userPath'] . $interface . '.php')) {
      require_once(self::$interface['userPath'] . $interface . '.php');
    } else
      if ($file->exists(self::$interface['systemPath'] . $interface . '.php')) {
        require_once(self::$interface['systemPath'] . $interface . '.php');
      } else {
        throw new \RuntimeException('Can not find interface ' . $interface);
      }
  }

  /**
   * API for checking the type of the given value.
   * Kills the program when the type of the variable is not the requested type
   *
   * @param string $type
   * @param object $value
   * @param boolean $required
   * @param array $values
   * @throws NullPointerException if $value is null and $s_type is not 'null'.
   * @throws TypeException if $value has the wrong type.
   */
  public static function type($type, $value, $required = false, array $values = [])
  {
    $oke = true;

    if (is_null($type)) {
      throw new \NullPointerException('Type can not be a null-pointer');
    }

    if ($type != 'null' && is_null($value)) {
      throw new \NullPointerException('Null found when expected ' . $type . '.');
    }

    switch ($type) {
      case 'bool':
      case 'boolean':
        if (!is_bool($value)) {
          $oke = false;
        }
        break;

      case 'int':
        if (!is_int($value)) {
          $oke = false;
        }
        break;

      case 'float':
        if (!is_float($value)) {
          $oke = false;
        }
        break;

      case 'string':
        if (!is_string($value)) {
          $oke = false;
        }
        break;

      case 'object':
        if (!is_object($value)) {
          $oke = false;
        }
        break;

      case 'array':
        if (!is_array($value)) {
          $oke = false;
        }
        break;

      case 'null':
        if (!is_null($value)) {
          $oke = false;
        }
        break;
    }

    if (!$oke) {
      throw new \TypeException('Wrong datatype found. Expected ' . $type . ' but found ' . gettype($value) . '.');
    }

    if (empty($value) && $required) {
      throw new \InvalidArgumentException('Required field is empty.');
    }
    if (count($values) > 0 && !in_array($value, $values)) {
      throw new \InvalidArgumentException('Value ' . $value . ' is invalid. Only the values ' . implode(', ', $values) . ' are allowed.');
    }
  }

  /**
   * Removes a value from the global memory
   *
   * @param string $type
   * @param string $name
   * @throws \RuntimeException If the value is not in the global memory
   * @deprecated since version 2
   */
  public static function delete($type, $name)
  {
    self::type('string', $type);
    self::type('string', $name);

    $type = strtolower($type);
    $name = ucfirst(strtolower($name));

    if (array_key_exists('\youconix\core\\' . $type . 's\\' . $name, self::$cache)) {
      unset(self::$cache['\youconix\core\\' . $type . 's\\' . $name]);
    } else
      if (array_key_exists('\includes\\' . $type . 's\\' . $name, self::$cache)) {
        unset(self::$cache['\includes\\' . $type . 's\\' . $name]);
      } else {
        throw new \RuntimeException("Trying to delete " . $type . " " . $name . " that does not exist");
      }
  }

  /**
   * Stops the framework and writes all the content to the screen
   */
  public static function endProgram()
  {
    self::reset();
    die();
  }

  /**
   * Resets Memory
   */
  public static function reset()
  {
    self::$testing = null;
    self::$service = null;
    self::$serviceData = null;
    self::$model = null;
    self::$modelData = null;
    self::$helper = null;
    self::$helperData = null;
    self::$class = null;
    self::$interface = null;
    self::$prettyUrls = null;
    self::$cache = null;
  }

  /**
   * Transforms a relative url into a absolute url (site domain only)
   *
   * @param string $url
   * @param array $payload
   * @return string The absolute url
   */
  public static function parseUrl($url, array $payload = [])
  {
    if (substr($url, 0, 1) != '/') {
      $url = '/' . $url;
    }

    if (self::$prettyUrls) {
      $url .= '/';
      $url .= implode('/', $payload);
    } else {
      if (substr($url, -1) == '/') {
        if (count($payload) > 0) {
          $url .= 'index.php?';
        }
      } else {
        if (strpos($url, '.php') === false) {
          $url .= '.php';
        }
        if (count($payload) > 0) {
          $url .= '?';
        }
      }
      $items = [];
      foreach ($payload as $key => $value) {
        $items[] = $key . '=' . $value;
      }
      $url .= implode('&amp;', $items);
    }
    return $url;
  }

  /**
   * Transforms a relative url into a absolute url (site domain only)
   *
   * @param string $url
   * @return string The absolute url
   * @deprecated See Core/self::parseUrl()
   */
  public static function generateUrl($url)
  {
    if (!self::isTesting()) {
      trigger_error("This function has been deprecated in favour of self::parseUrl().", E_USER_DEPRECATED);
    }
    return self::parseUrl($url);
  }

  /**
   * Redirects the visitor to the given site url
   *
   * @param string $url
   * @param array $payload
   */
  public static function redirect($url, array $payload = [])
  {
    $url = self::parseUrl($url, $payload);
    header('location: ' . $url);
    exit();
  }
}

if (!function_exists('class_alias')) {

  function class_alias($original, $alias)
  {
    eval('class ' . $alias . ' extends ' . $original . ' {}');
  }
}
