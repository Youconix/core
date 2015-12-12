<?php
namespace youconix\core;

if (! class_exists('\youconix\coreException')) {
    require (WEB_ROOT.DIRECTORY_SEPARATOR . CORE . 'exceptions/CoreException.php');
}

/**
 * Memory-handler for controlling memory and autostarting the framework
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
 */
class Memory
{

    private static $bo_testing = false;

    private static $a_service;

    private static $a_serviceData;

    private static $a_model;

    private static $a_modelData;

    private static $a_helper;

    private static $a_helperData;

    private static $a_class;

    private static $a_interface;

    private static $a_database;

    private static $bo_prettyUrls;

    private static $a_cache;

    /**
     * Destructor
     */
    public function __destruct()
    {
        Memory::reset();
    }

    /**
     * Starts the framework in testing mode.
     * DO NOT USE THIS IN PRODUCTION
     */
    public static function setTesting()
    {
        Memory::$bo_testing = true;
        if (! defined('DEBUG')) {
            define('DEBUG', 'true');
        }
        
        if (! defined('PROCESS')) {
            define('PROCESS', 1);
        }
        
        Memory::startUp();
    }

    /**
     * Starts the framework
     */
    public static function startUp()
    {
        if (! is_null(Memory::$a_cache)) {
            return;
        }
        
        \Profiler::profileSystem('core/Memory', 'Framework start');
        
        if (! defined('DS')) {
            define('DS', DIRECTORY_SEPARATOR);
        }
        
        try {
            if (! defined('DATA_DIR')) {
                if (Memory::$bo_testing) {
                    define('DATA_DIR', NIV . 'admin' . DS . 'data' . DS . 'tests' . DS);
                } else {
                    define('DATA_DIR', NIV . 'admin' . DS . 'data' . DS);
                }
            }
            
            /* Prepare cache */
            Memory::$a_cache = array();
            
            Memory::$a_service = array(
                'systemPath' => NIV . CORE . 'services' . DS,
                'userPath' => NIV . 'includes' . DS . 'services' . DS,
                'systemNamespace' => '\youconix\core\services\\',
                'userNamespace' => '\includes\services\\'
            );
            Memory::$a_serviceData = array(
                'systemPath' => NIV . CORE . 'services' . DS . 'data' . DS,
                'userPath' => NIV . 'includes' . DS . 'services' . DS . 'data' . DS,
                'systemNamespace' => '\youconix\core\services\data\\',
                'userNamespace' => '\includes\services\data\\'
            );
            Memory::$a_model = array(
                'systemPath' => NIV . CORE . 'models' . DS,
                'userPath' => NIV . 'includes' . DS . 'models' . DS,
                'systemNamespace' => '\youconix\core\models\\',
                'userNamespace' => '\includes\models\\'
            );
            Memory::$a_modelData = array(
                'systemPath' => NIV . CORE . 'models' . DS . 'data' . DS,
                'userPath' => NIV . 'includes' . DS . 'models' . DS . 'data' . DS,
                'systemNamespace' => '\youconix\core\models\data\\',
                'userNamespace' => '\includes\models\data\\'
            );
            Memory::$a_helper = array(
                'systemPath' => NIV . CORE . 'helpers' . DS,
                'userPath' => NIV . 'includes' . DS . 'helpers' . DS,
                'systemNamespace' => '\youconix\core\helpers\\',
                'userNamespace' => '\includes\helpers\\'
            );
            Memory::$a_helperData = array(
                'systemPath' => NIV . CORE . 'helpers' . DS . 'data' . DS,
                'userPath' => NIV . 'includes' . DS . 'helpers' . DS . 'data' . DS,
                'systemNamespace' => '\youconix\core\helpers\data\\',
                'userNamespace' => '\includes\helpers\data\\'
            );
            Memory::$a_class = array(
                'systemPath' => NIV . CORE . 'classes' . DS,
                'userPath' => NIV . 'includes' . DS . 'classes' . DS,
                'systemNamespace' => '\youconix\core\classes\\',
                'userNamespace' => '\includes\classes\\'
            );
            Memory::$a_interface = array(
                'systemPath' => NIV . CORE . 'interfaces' . DS,
                'userPath' => NIV . 'includes' . DS . 'interfaces' . DS,
                'systemNamespace' => '\youconix\core\interfaces\\',
                'userNamespace' => '\includes\interfaces\\'
            );
            Memory::$a_database = array(
                'systemPath' => NIV . CORE . 'database' . DS,
                'userPath' => NIV . 'includes' . DS . 'database' . DS,
                'systemNamespace' => '\youconix\core\database\\',
                'userNamespace' => '\includes\database\\'
            );
            
            Memory::loadCoreClass(NIV . CORE . 'Object.php');
            Memory::loadCoreClass(Memory::$a_service['systemPath'] . 'Service.php');
            Memory::loadCoreClass(Memory::$a_model['systemPath'] . 'Model.php');
            Memory::loadCoreClass(Memory::$a_helper['systemPath'] . 'Helper.php');
            Memory::loadCoreClass(NIV . CORE . 'Loader.php');
            Memory::loadCoreClass(NIV . CORE . 'IoC.php');
            
            /* Load standard services */
            $caller = IoC::$s_ruleFileHandler;
            $service_File = new $caller();
            Memory::$a_cache[$caller] = $service_File;
            if (! $service_File->exists(NIV . 'files' . DS . 'updates')) {
                $service_File->newDirectory(NIV . 'files' . DS . 'updates', 0700);
            }
            
            unset($caller);
            
            $caller = IoC::$s_ruleSettings;
            $service_Settings = \Loader::Inject($caller);
            Memory::$a_cache[$caller] = $service_Settings;
            Memory::$a_cache['\Settings'] = $service_Settings;
            Memory::$a_cache['\youconix\core\services\XmlSettings'] = $service_Settings;
            unset($caller);
            date_default_timezone_set($service_Settings->get('settings/main/timeZone'));
            
            /* Load IoC */
            $caller = '\youconix\core\IoC';
            if (file_exists(NIV . 'includes/IoC.php')) {
                require_once (NIV . 'includes/IoC.php');
                $caller = '\includes\IoC';
            }
            $IoC = new $caller($service_Settings);
            Memory::$a_cache['IoC'] = $IoC;
            
            Memory::setDefaultValues($service_Settings);
            
            \Profiler::profileSystem('core/Memory', 'Framework startup complete');
        } catch (\Exception $e) {
            throw new \CoreException('Starting up framework failed', 0, $e);
        }
    }

    private static function loadCoreClass($s_filename)
    {
        if (file_exists(str_replace('core', 'files' . DS . 'updates' . DS . 'core', $s_filename))) {
            require_once (file_exists(str_replace('core', 'files' . DS . 'updates' . DS . 'core', $s_filename)));
        } else {
            require_once ($s_filename);
        }
    }

    /**
     * Sets the default values
     *
     * @param \youconix\core\services\Settings $service_Settings
     *            The settings service
     */
    private static function setDefaultValues($service_Settings)
    {
        if (isset($_SERVER['SERVER_ADDR']) && in_array($_SERVER['SERVER_ADDR'], array(
            '127.0.0.1',
            '::1'
        )) && ! defined('DEBUG')) {
            define('DEBUG', null);
        }
        
        if (defined('DEBUG')) {
            ini_set('display_errors', 'on');
        } else {
            ini_set('display_errors', 'off');
        }
        
        error_reporting(E_ALL);
        
        Memory::$bo_prettyUrls = false;
        if ($service_Settings->exists('settings/main/pretty_urls') && $service_Settings->get('settings/main/pretty_urls') == 1) {
            Memory::$bo_prettyUrls = true;
        }
    }

    /**
     * Checks if the class is in the cache
     *
     * @param string $s_name
     *            The namespace and object name
     * @return boolean True if the class is in the cache
     */
    public static function IsInCache($s_name)
    {
        return array_key_exists($s_name, Memory::$a_cache);
    }

    /**
     * Sets the object in the cache
     *
     * @param string $s_name
     *            The namespace and object name
     * @param Object $object
     *            The object
     */
    public static function setCache($s_name, $object)
    {
        Memory::$a_cache[$s_name] = $object;
    }

    /**
     * Returns the object from the cache
     *
     * @param string $s_name
     *            The namespace and object name
     * @return Object The object or null
     */
    public static function getCache($s_name)
    {
        if (! Memory::IsInCache($s_name)) {
            return null;
        }
        
        return Memory::$a_cache[$s_name];
    }

    /**
     * Returns the used protocol
     *
     * @return String protocol
     * @deprecated since version 2.
     * @see core/models/Config:getProtocol
     */
    public static function getProtocol()
    {
        if (! Memory::isTesting()) {
            trigger_error("This function has been deprecated in favour of core/models/Config->getProtocol().", E_USER_DEPRECATED);
        }
        return Memory::$a_cache['\youconix\core\models\Config']->getProtocol();
    }

    /**
     * Returns the current page
     *
     * @return String page
     * @deprecated since version 2.
     * @see core/models/Config:getPage
     */
    public static function getPage()
    {
        if (! Memory::isTesting()) {
            trigger_error("This function has been deprecated in favour of core/models/Config->getPage().", E_USER_DEPRECATED);
        }
        return Memory::$a_cache['\youconix\core\models\Config']->getPage();
    }

    /**
     * Checks if ajax-mode is active
     *
     * @return boolean if ajax-mode is active
     * @deprecated since version 2.
     * @see core/models/Config:isAjax
     */
    public static function isAjax()
    {
        if (! Memory::isTesting()) {
            trigger_error("This function has been deprecated in favour of core/models/Config->isAjax().", E_USER_DEPRECATED);
        }
        return Memory::$a_cache['\youconix\core\models\Config']->isAjax();
    }

    /**
     * Sets the framework in ajax-
     *
     * @deprecated since version 2
     * @see core/models/Config::setAjax()
     */
    public static function setAjax()
    {
        if (! Memory::isTesting()) {
            trigger_error("This function has been deprecated in favour of core/models/Config->setAjax().", E_USER_DEPRECATED);
        }
        Memory::$a_cache['\youconix\core\models\Config']->setAjax();
    }

    /**
     * Checks if testing-mode is active
     *
     * return boolean True if testing-mode is active
     */
    public static function isTesting()
    {
        return Memory::$bo_testing;
    }

    /**
     * Returns the base directory
     *
     * @return String directory
     * @deprecated since version 2.
     *             @See core/models/Config:getBase
     */
    public static function getBase()
    {
        if (! Memory::isTesting()) {
            trigger_error("This function has been deprecated in favour of core/models/Config->getBase().", E_USER_DEPRECATED);
        }
        return Memory::$a_cache['\youconix\core\models\Config']->getBase();
    }

    /**
     * Ensures that the given class is loaded
     *
     * @param String $s_class
     *            class name
     * @deprecated
     *
     * @throws RuntimeException the class does not exists in include/class/
     */
    public static function ensureClass($s_class)
    {
        if (! Memory::isTesting()) {
            trigger_error("This function has been deprecated.", E_USER_DEPRECATED);
        }
        
        if (! class_exists($s_class)) {
            $service_File = Memory::$a_cache['\youconix\core\services\File'];
            if (! $service_File->exists(Memory::$a_class['systemPath'] . $s_class . '.inc.php')) {
                throw new \RuntimeException('Can not find class ' . $s_class);
            }
            
            require_once (Memory::$a_class['systemPath'] . $s_class . '.inc.php');
        }
    }

    /**
     * Ensures that the given interface is loaded
     *
     * @param String $s_interface
     *            interface name
     * @deprecated
     *
     * @throws RuntimeException the interface does not exists in include/interface/
     */
    public static function ensureInterface($s_interface)
    {
        if (! Memory::isTesting()) {
            trigger_error("This function has been deprecated.", E_USER_DEPRECATED);
        }
        if (! interface_exists($s_interface)) {
            $service_File = Memory::$a_cache['\youconix\core\services\File'];
            if (! $service_File->exists(Memory::$a_interface['systemPath'] . $s_interface . '.inc.php')) {
                throw new \RuntimeException('Can not find interface ' . $s_interface);
            }
            
            require_once (Memory::$a_interface['systemPath'] . $s_interface . '.inc.php');
        }
    }

    /**
     * Checks if a file is a core module
     *
     * @param unknown $s_name            
     * @param unknown $a_memoryItems            
     * @param unknown $s_path            
     * @param unknown $s_namespace            
     * @param string $s_fallback            
     * @deprecated
     *
     * @return boolean
     */
    private static function isModule($s_name, $a_memoryItems, $s_path, $s_namespace, $s_fallback = '')
    {
        if (! Memory::isTesting()) {
            trigger_error("This function has been deprecated.", E_USER_DEPRECATED);
        }
        $s_name = ucfirst($s_name);
        
        /* Call class file */
        $service_File = Memory::$a_cache['\youconix\core\services\File'];
        $s_path = $s_path . '/' . $s_name . '.php';
        
        if (! $service_File->exists($s_path)) {
            return false;
        }
        
        $s_item = $service_File->readFile($s_path);
        
        if ((strpos($s_item, 'namespace ' . $s_namespace . ';') !== false) || (strpos($s_item, 'namespace ' . $s_namespace . '\data;') !== false)) {
            return true;
        }
        
        if (! empty($s_fallback) && (strpos($s_item, 'class ' . $s_fallback . '_' . $s_name) !== false)) {
            return true;
        }
        
        return false;
    }

    /**
     * Loads the requested module
     * Automaticaly overrides the system module with the user defined one
     *
     * @param String $s_name
     *            The name of the module
     * @param String $s_memoryType
     *            The memory-type that the data is saved in
     * @param String $a_data
     *            The locations array
     * @param String $s_fallback
     *            The fallback class type (V1)
     * @return Object The module
     * @deprecated
     *
     * @throws \RuntimeException If the requested module does not exist
     * @throws \OverrideException If the override module is not a child of the system module
     */
    private static function loadModule($s_name, $s_memoryType, $a_data, $s_fallback = '')
    {
        if (! Memory::isTesting()) {
            trigger_error("This function has been deprecated.", E_USER_DEPRECATED);
        }
        $s_name = ucfirst($s_name);
        
        $object = \Loader::Inject($a_data['systemNamespace'] . $s_name);
        
        if (is_null($object)) {
            if (! empty($s_fallback) && class_exists($s_fallback . '_' . $s_name)) {
                $s_caller = $s_fallback . '_' . $s_name;
                $object = new $s_caller();
            } else {
                throw new \RuntimeException('Can not find ' . $s_memoryType . ' ' . $s_name);
            }
        }
        
        return $object;
    }

    /**
     * API for checking or a helper exists
     *
     * @param String $s_name
     *            The name of the helper
     * @param bool $bo_data
     *            to true to use the data directory
     * @deprecated
     *
     * @return bool if the helper exists, otherwise false
     */
    public static function isHelper($s_name, $bo_data = false)
    {
        if (! Memory::isTesting()) {
            trigger_error("This function has been deprecated.", E_USER_DEPRECATED);
        }
        $a_memoryItems = array(
            'helper',
            'helper-data'
        );
        $s_fallback = 'Helper';
        
        if ($bo_data) {
            $s_path = Memory::$a_helper['systemPath'] . 'data/';
            $s_namespace = 'core\helpers\data';
        } else {
            $s_path = Memory::$a_helper['systemPath'];
            $s_namespace = 'core\helpers';
        }
        
        return Memory::isModule($s_name, $a_memoryItems, $s_path, $s_namespace, $s_fallback);
    }

    /**
     * Loads the requested helper
     *
     * @param String $s_name
     *            The name of the helper
     * @param bool $bo_data
     *            to true to use the data directory
     * @return Helper The requested helper
     * @deprecated
     *
     * @throws Exception If the requested helper does not exist
     * @throws \OverrideException If the override module is not a child of the system module
     */
    public static function helpers($s_name, $bo_data = false)
    {
        if (! Memory::isTesting()) {
            trigger_error("This function has been deprecated.", E_USER_DEPRECATED);
        }
        if ($bo_data) {
            return Memory::loadModule($s_name, 'helper-data', Memory::$a_helperData);
        }
        
        $a_data = Memory::$a_helper;
        if ($s_name == 'HTML') {
            $a_data['systemNameSpace'] = '\youconix\core\helpers\html\\';
        }
        
        return Memory::loadModule($s_name, 'helper', $a_data, 'Helper');
    }

    /**
     * API for checking or a service exists
     *
     * @param String $s_name
     *            The name of the service
     * @param bool $bo_data
     *            to true to use the data directory
     * @deprecated
     *
     * @return bool if the service exists, otherwise false
     */
    public static function isService($s_name, $bo_data = false)
    {
        if (! Memory::isTesting()) {
            trigger_error("This function has been deprecated, and will be removed after version 3.", E_USER_DEPRECATED);
        }
        $a_memoryItems = array(
            'service',
            'service-data'
        );
        if ($bo_data) {
            $s_path = Memory::$a_service['systemPath'] . 'data/';
            $s_namespace = 'core\services\data';
        } else {
            $s_path = Memory::$a_service['systemPath'];
            $s_namespace = 'core\services';
        }
        $s_fallback = 'Service';
        
        return Memory::isModule($s_name, $a_memoryItems, $s_path, $s_namespace, $s_fallback);
    }

    /**
     * Loads the requested service
     *
     * @param String $s_name
     *            The name of the service
     * @param bool $bo_data
     *            to true to use the data directory
     * @return Service The requested service
     * @deprecated
     *
     * @throws Exception If the requested service does not exist
     * @throws \OverrideException If the override module is not a child of the system module
     */
    public static function services($s_name, $bo_data = false)
    {
        if (! Memory::isTesting()) {
            trigger_error("This function has been deprecated in favour of \Loader::inject().", E_USER_DEPRECATED);
        }
        if ($bo_data) {
            return Memory::loadModule($s_name, 'service', Memory::$a_serviceData);
        }
        
        return Memory::loadModule($s_name, 'service', Memory::$a_service, 'Service');
    }

    /**
     * API for checking or a model exists
     *
     * @param String $s_name
     *            The name of the model
     * @param bool $bo_data
     *            to true to use the data directory
     * @deprecated
     *
     * @return bool if the model exists, otherwise false
     */
    public static function isModel($s_name, $bo_data = false)
    {
        if (! Memory::isTesting()) {
            trigger_error("This function has been deprecated, and will be removed following version 3.", E_USER_DEPRECATED);
        }
        $a_memoryItems = array(
            'model',
            'model-data'
        );
        if ($bo_data) {
            $s_path = Memory::$a_model['systemPath'] . 'data/';
            $s_namespace = 'core\models\data';
        } else {
            $s_path = Memory::$a_model['systemPath'];
            $s_namespace = 'core\models';
        }
        $s_fallback = 'Model';
        
        return Memory::isModule($s_name, $a_memoryItems, $s_path, $s_namespace, $s_fallback);
    }

    /**
     * Loads the requested model
     *
     * @param String $s_name
     *            The name of the model
     * @param bool $bo_data
     *            to true to use the data directory
     * @return Model The requested model
     * @deprecated
     *
     * @throws Exception If the requested model does not exist
     * @throws \OverrideException If the override module is not a child of the system module
     */
    public static function models($s_name, $bo_data = false)
    {
        if (! Memory::isTesting()) {
            trigger_error("This function has been deprecated in favour of \Loader:inject().", E_USER_DEPRECATED);
        }
        if ($bo_data) {
            return Memory::loadModule($s_name, 'model-data', Memory::$a_modelData);
        }
        
        return Memory::loadModule($s_name, 'model', Memory::$a_model);
    }

    /**
     * Checks if a helper, service or model is loaded
     *
     * @param String $s_type
     *            The type (helper|helper-data|model|model-data|service|service-data)
     * @param String $s_name
     *            name of the object
     * @deprecated
     *
     * @return boolean if the value exists in the memory, false if it does not
     */
    public static function isLoaded($s_type, $s_name)
    {
        if (! Memory::isTesting()) {
            trigger_error("This function has been deprecated.", E_USER_DEPRECATED);
        }
        $s_type = strtolower($s_type);
        $s_name = ucfirst($s_name);
        
        if (array_key_exists('\youconix\core\\' . $s_type . 's\\' . $s_name, Memory::$a_cache)) {
            return true;
        }
        
        return false;
    }

    /**
     * Loads a class
     *
     * @param String $s_class
     *            The class name
     * @return Object The Object
     * @throws \RuntimeException If the class does not exist
     * @deprecated
     *
     * @throws \OverrideException the override class is not a child of the default class
     */
    public static function loadClass($s_class)
    {
        if (! Memory::isTesting()) {
            trigger_error("This function has been deprecated.", E_USER_DEPRECATED);
        }
        $s_class = ucfirst($s_class);
        
        /* Check model */
        $service_File = Memory::$a_cache['\youconix\core\services\File'];
        $bo_override = false;
        $s_path = '';
        $s_caller = '';
        $s_callerParent = '';
        
        if ($service_File->exists(Memory::$a_class['userPath'] . $s_class . '.php')) {
            if ($service_File->exists(Memory::$a_class['systemPath'] . $s_class . '.php')) {
                $bo_override = true;
                $s_callerParent = Memory::$a_class['systemNamespace'] . $s_class;
                
                if (! class_exists($s_callerParent)) {
                    require (Memory::$a_class['systemPath'] . $s_class . '.php');
                }
            }
            
            require_once (Memory::$a_class['userPath'] . $s_class . '.php');
            $s_path = Memory::$a_class['userPath'] . $s_class . '.php';
            $s_caller = Memory::$a_class['userNamespace'] . $s_class;
        } else 
            if ($service_File->exists(Memory::$a_class['systemPath'] . $s_class . '.php')) {
                require_once (Memory::$a_class['systemPath'] . $s_class . '.php');
                $s_path = Memory::$a_class['systemPath'] . $s_class . '.php';
                $s_caller = Memory::$a_class['systemNamespace'] . $s_class;
            } else {
                throw new \RuntimeException('Can not find class ' . $s_class);
            }
        
        $object = \Loader::Inject($s_caller);
        
        if (! empty($s_callerParent) && ! ($object instanceof $s_callerParent)) {
            throw new \OverrideException('Override ' . $s_caller . ' is not a child of ' . $s_callerParent . '.');
        }
        return $object;
    }

    /**
     * Loads an interface
     *
     * @param String $s_interface
     *            interface
     * @deprecated
     *
     * @throws \RuntimeException If the interface does not exist
     */
    public static function loadInterface($s_interface)
    {
        if (! Memory::isTesting()) {
            trigger_error("This function has been deprecated.", E_USER_DEPRECATED);
        }
        $s_interface = ucfirst($s_interface);
        
        /* Check model */
        $service_File = Memory::$a_cache['\youconix\core\services\File'];
        
        if ($service_File->exists(Memory::$a_interface['userPath'] . $s_interface . '.php')) {
            require_once (Memory::$a_interface['userPath'] . $s_interface . '.php');
        } else 
            if ($service_File->exists(Memory::$a_interface['systemPath'] . $s_interface . '.php')) {
                require_once (Memory::$a_interface['systemPath'] . $s_interface . '.php');
            } else {
                throw new \RuntimeException('Can not find interface ' . $s_interface);
            }
    }

    /**
     * API for checking the type of the given value.
     * Kills the program when the type of the variable is not the requested type
     *
     * @param String $s_type
     *            the variable schould be
     * @param object $value
     *            that needs to be checked
     * @param boolean $bo_required
     *            Set to true to make the value required
     * @param array $a_values
     *            Set of valid values
     * @throws NullPointerException if $value is null and $s_type is not 'null'.
     * @throws TypeException if $value has the wrong type.
     */
    public static function type($s_type, $value, $bo_required = false, $a_values = array())
    {
        $bo_oke = true;
        
        if (is_null($s_type)) {
            throw new \NullPointerException('Type can not be a null-pointer');
        }
        
        if ($s_type != 'null' && is_null($value)) {
            throw new \NullPointerException('Null found when expected ' . $s_type . '.');
        }
        
        switch ($s_type) {
            case 'bool':
            case 'boolean':
                if (! is_bool($value)) {
                    $bo_oke = false;
                }
                break;
            
            case 'int':
                if (! is_int($value)) {
                    $bo_oke = false;
                }
                break;
            
            case 'float':
                if (! is_float($value)) {
                    $bo_oke = false;
                }
                break;
            
            case 'string':
                if (! is_String($value)) {
                    $bo_oke = false;
                }
                break;
            
            case 'object':
                if (! is_object($value)) {
                    $bo_oke = false;
                }
                break;
            
            case 'array':
                if (! is_array($value)) {
                    $bo_oke = false;
                }
                break;
            
            case 'null':
                if (! is_null($value)) {
                    $bo_oke = false;
                }
                break;
        }
        
        if (! $bo_oke) {
            throw new \TypeException('Wrong datatype found. Expected ' . $s_type . ' but found ' . gettype($value) . '.');
        }
        
        if (empty($value) && $bo_required) {
            throw new \InvalidArgumentException('Required field is empty.');
        }
        if (! is_array($a_values)) {
            $a_values = array(
                $a_values
            );
        }
        if (count($a_values) > 0 && ! in_array($value, $a_values)) {
            throw new \InvalidArgumentException('Value ' . $value . ' is invalid. Only the values ' . implode(', ', $a_values) . ' are allowed.');
        }
    }

    /**
     * Removes a value from the global memory
     *
     * @param String $s_type
     *            The type (helper|helper-data|model|model-data|service|service-data)
     * @param String $s_name
     *            The name of the data
     * @throws RuntimeException If the value is not in the global memory
     */
    public static function delete($s_type, $s_name)
    {
        \youconix\core\Memory::type('String', $s_type);
        \youconix\core\Memory::type('String', $s_name);
        
        $s_type = strtolower($s_type);
        $s_name = ucfirst(strtolower($s_name));
        
        if (array_key_exists('\youconix\core\\' . $s_type . 's\\' . $s_name, Memory::$a_cache)) {
            unset(Memory::$a_cache['\youconix\core\\' . $s_type . 's\\' . $s_name]);
        } else 
            if (array_key_exists('\includes\\' . $s_type . 's\\' . $s_name, Memory::$a_cache)) {
                unset(Memory::$a_cache['\includes\\' . $s_type . 's\\' . $s_name]);
            } else {
                throw new \RuntimeException("Trying to delete " . $s_type . " " . $s_name . " that does not exist");
            }
    }

    /**
     * Stops the framework and writes all the content to the screen
     */
    public static function endProgram()
    {
        \Profiler::dump();
        
        $service_Template = null;
        if (array_key_exists('\youconix\core\templating\Template', Memory::$a_cache)) {
            $service_Template = Memory::$a_cache['\youconix\core\templating\Template'];
        } else 
            if (array_key_exists('\includes\templating\Template', Memory::$a_cache)) {
                $service_Template = Memory::$a_cache['\includes\templating\Template'];
            }
        
        if (! is_null($service_Template)) {
            $service_Template->printToScreen();
        }
        
        die();
    }

    /**
     * Resets Memory
     */
    public static function reset()
    {
        Memory::$bo_testing = null;
        
        Memory::$a_service = null;
        Memory::$a_serviceData = null;
        Memory::$a_model = null;
        Memory::$a_modelData = null;
        Memory::$a_helper = null;
        Memory::$a_helperData = null;
        Memory::$a_class = null;
        Memory::$a_interface = null;
        Memory::$bo_prettyUrls = null;
        Memory::$a_cache = null;
        
        \Profiler::reset();
    }

    /**
     * Transforms a relative url into a absolute url (site domain only)
     *
     * @param String $s_url
     *            The relative url
     * @param array $a_payload
     *            The fields to send with the url, optional
     * @return String The absolute url
     */
    public static function parseUrl($s_url, $a_payload = array())
    {
        if (substr($s_url, 0, 1) != '/') {
            $s_url = '/' . $s_url;
        }
        
        if (Memory::$bo_prettyUrls) {
            $s_url .= '/';
            $s_url .= implode('/', $a_payload);
        } else {
            if (substr($s_url, - 1) == '/') {
                if (count($a_payload) > 0) {
                    $s_url .= 'index.php?';
                }
            } else {
                if (strpos($s_url, '.php') === false) {
                    $s_url .= '.php';
                }
                if (count($a_payload) > 0) {
                    $s_url .= '?';
                }
            }
            $a_items = array();
            foreach ($a_payload as $s_key => $s_value) {
                $a_items[] = $s_key . '=' . $s_value;
            }
            $s_url .= implode('&amp;', $a_items);
        }
        return $s_url;
    }

    /**
     * Transforms a relative url into a absolute url (site domain only)
     *
     * @param String $s_url
     *            The relative url
     * @return String The absolute url
     * @deprecated See core/Memory::parseUrl()
     */
    public static function generateUrl($s_url)
    {
        if (! Memory::isTesting()) {
            trigger_error("This function has been deprecated in favour of Memory::parseUrl().", E_USER_DEPRECATED);
        }
        return Memory::parseUrl($s_url);
    }

    /**
     * Redirects the visitor to the given site url
     *
     * @param String $s_url
     *            The relative url
     * @param array $a_payload
     *            The fields to send with the url, optional
     */
    public static function redirect($s_url, $a_payload = array())
    {
        $s_url = Memory::parseUrl($s_url, $a_payload);
        header('location: ' . $s_url);
        exit();
    }

    /**
     * Detects the system base
     * Should only be used on install (redirection)
     *
     * @throws \RuntimeException the base could not be found
     * @return string base
     */
    public static function detectBase()
    {
        $s_url = $_SERVER['SCRIPT_NAME'];
        if (substr($s_url, 0, 1) == '/') {
            $s_url = substr($s_url, 1);
        }
        $s_url = substr($s_url, 0, strrpos($s_url, '/'));
        
        $a_dirs = explode('/', $s_url);
        $s_wwwRoot = $_SERVER['DOCUMENT_ROOT'];
        
        /* Seach for Memory */
        $s_base = '';
        $i_pos = 0;
        $i_max = count($a_dirs);
        
        while ($i_pos <= $i_max) {
            if (file_exists($s_wwwRoot . '/' . $s_base . '/include/Memory.php')) {
                return '/' . $s_base;
            }
            
            if (! empty($s_base)) {
                $s_base .= '/';
            }
            $s_base .= $a_dirs[$i_pos];
            $i_pos ++;
        }
        
        throw new \RuntimeException('Unable to detect website root for ' . $_SERVER['SCRIPT_NAME'] . '.');
    }
}

if (! function_exists('class_alias')) {

    function class_alias($original, $alias)
    {
        eval('class ' . $alias . ' extends ' . $original . ' {}');
    }
}
