<?php

/**
 * General class loader and dependency injection
 * 
 * @author Roxanna Lugtigheid
 * @link        http://www.php-fig.org/psr/psr-4/
 */
class Loader
{

    private static function getFileName($s_className)
    {
        /* Check for interfaces */
        if (file_exists(NIV.CORE .'interfaces' . DS . $s_className . '.inc.php')) {
            return CORE . 'interfaces' . DS . $s_className . '.inc.php';
        }
        if (file_exists(NIV . 'includes' . DS . 'interfaces' . DS . $s_className . '.inc.php')) {
            return 'includes' . DS . 'interfaces' . DS . $s_className . '.inc.php';
        }
        
        $s_className = ltrim($s_className, '\\');
        $s_fileName = '';
        $s_namespace = '';
        if ($i_lastNsPos = strrpos($s_className, '\\')) {
            $s_namespace = substr($s_className, 0, $i_lastNsPos);
            $s_className = substr($s_className, $i_lastNsPos + 1);
            $s_fileName = str_replace('\\', DS, $s_namespace) . DS;
        }
        
        if( file_exists(NIV.str_replace('core/',CORE,$s_fileName).DS.$s_className.'.inc.php') ){
            return str_replace('core/',CORE,$s_fileName).DS.$s_className.'.inc.php';
        }
        
        if (file_exists(NIV . $s_fileName.DS.$s_className.'.inc.php')) {
            return $s_fileName.DS.$s_className.'.inc.php';
        }
        
        if( file_exists(NIV.str_replace('core/',CORE,$s_fileName).DS.$s_className.'.php') ){
            return str_replace('core/',CORE,$s_fileName).DS.$s_className.'.php';
        }
        
        if (file_exists(NIV . $s_fileName.DS.$s_className.'.php')) {
            return $s_fileName.DS.$s_className.'.php';
        }
        
        /* Check for website files */
        $s_name = strtolower($s_fileName . DS . $s_className . '.php');
        if (file_exists(NIV . $s_name)) {
            return $s_name;
        }
        
        if (defined('WEBSITE_ROOT')) {
            if (file_exists(WEBSITE_ROOT . $s_name.'.php')) {
                return WEBSITE_ROOT . $s_name.'.php';
            }
        }
        
        /* Check for libs */
        $s_fileName .= str_replace('_', DS, $s_className) . '.php';
        if (file_exists(NIV . 'vendor' . DS . $s_fileName)) {
            return 'vendor' . DS . $s_fileName;
        }
        if (file_exists(NIV . 'vendor' . DS . str_replace($s_namespace, strtolower(str_replace('\\', DS, $s_namespace)), $s_fileName))) {
            return 'vendor' . DS . str_replace($s_namespace, strtolower(str_replace('\\', DS, $s_namespace)), $s_fileName);
        }
        if (file_exists(NIV . 'vendor' . DS . strtolower(str_replace('\\', DS, $s_namespace)) . DS . $s_fileName)) {
            return 'vendor' . DS . strtolower(str_replace('\\', DS, $s_namespace)) . DS . $s_fileName;
        }
        
        return null;
    }

    public static function autoload($s_className)
    {
        if (preg_match('/Exception$/', $s_className)) {
            $s_fileName = null;
            if (file_exists(NIV . DS . 'core' . DS . 'exceptions' . DS . $s_className . '.inc.php')) {
                $s_fileName = NIV . DS . 'core' . DS . 'exceptions' . DS . $s_className . '.inc.php';
            }
        } else {
            $s_fileName = Loader::getFileName($s_className);
        }
        
        if (! is_null($s_fileName)) {
            if ((substr($s_fileName, 0, 5) == 'core/') && file_exists(NIV . 'files' . DS . 'updates' . DS . $s_fileName)) {
                require (NIV . 'files' . DS . 'updates' . DS . $s_fileName);
            } 
            else {
                require NIV . $s_fileName;
            }
        }
    }

    public static function Inject($s_className, $a_arguments = array())
    {
        \Profiler::profileSystem('core/Loader.php', 'Loading class ' . $s_className);
        
        $s_fileName = null;
        /* Check IoC */
        $IoC = \core\Memory::getCache('IoC');
        if (! is_null($IoC)) {
            $check = $IoC::check($s_className);
            if (! is_null($check)) {
                $s_className = $check;
                if ((strpos($check, 'core\\') !== false) || (strpos($check, 'includes\\') !== false)) {
                    $s_fileName = str_replace('\\', DS, $check) . '.inc.php';
                } else {
                    $s_fileName = str_replace('\\', DS, $check) . '.php';
                }
                
                while (substr($s_fileName, 0, 1) == DS) {
                    $s_fileName = substr($s_fileName, 1);
                }
            }
        }
        
        if (is_null($s_fileName)) {
            $s_fileName = Loader::getFileName($s_className);
        }
        
        if (is_null($s_fileName)) {
            return null;
        }
        
        $s_caller = $s_className;
        
        if (substr($s_caller, 0, 1) != '\\') {
            $s_caller = '\\' . $s_caller;
        }
        
        if (! class_exists($s_caller) && ! interface_exists($s_caller)) {
            require (NIV . $s_fileName);
        }
        if ((substr($s_fileName, 0, 5) == 'core/') && (file_exists(NIV . str_replace('core/', 'includes/', $s_fileName) . '.inc.php'))) {
            $s_fileName = str_replace('core\\', 'includes\\', $s_fileName);
            $caller = str_replace('\core', '\includes', $caller);
            
            if (! class_exists($s_caller)) {
                require (NIV . $s_fileName);
            }
        }
        
        $object = Loader::injection($s_caller, NIV . $s_fileName, $a_arguments);
        
        return $object;
    }

    /**
     * Performs the dependency injection
     *
     * @param String $s_caller
     *            class name
     * @param String $s_filename
     *            source file name
     * @throws RuntimeException the object is not instantiable.
     * @return Object called object
     */
    private static function injection($s_caller, $s_filename, $a_argumentsGiven)
    {
        $ref = new \ReflectionClass($s_caller);
        if (! $ref->isInstantiable()) {
            /* Check cache */
            if (\core\Memory::IsInCache($s_caller)) {
                return \core\Memory::getCache($s_caller);
            }
            
            throw new \RuntimeException('Can not create a object from class ' . $s_caller . '.');
        }
        
        $bo_singleton = false;
        if (method_exists($s_caller, 'isSingleton') && $s_caller::isSingleton()) {
            /* Check cache */
            if (\core\Memory::IsInCache($s_caller)) {
                return \core\Memory::getCache($s_caller);
            } else {
                $bo_singleton = true;
            }
        }
        
        $a_matches = Loader::getConstructor($s_filename);
        
        if (count($a_matches) == 0) {
            /* No arguments */
            return new $s_caller();
        }
        $a_argumentNamesPre = explode(',', $a_matches[1]);
        
        $a_argumentNames = array();
        $a_arguments = array();
        
        foreach ($a_argumentNamesPre as $s_name) {
            $s_name = trim($s_name);
            if (strpos($s_name, ' ') === false) {
                continue;
            }
            if (substr($s_name, 0, 1) == '\\') {
                $s_name = substr($s_name, 1);
            }
            
            $a_item = explode(' ', $s_name);
            $a_argumentNames[] = $a_item[0];
        }
        
        foreach ($a_argumentNames as $s_name) {
            $a_path = explode('\\', $s_name);
            
            if (count($a_path) == 1) {
                /* No namespace */
                if (strpos($s_name, 'Helper_') !== false) {
                    $s_name = str_replace('Helper_', '', $s_name);
                    $a_arguments[] = \core\Memory::helpers($s_name);
                } else 
                    if (strpos($s_name, 'Service_') !== false) {
                        $s_name = str_replace('Service_', '', $s_name);
                        $a_arguments[] = \core\Memory::services($s_name);
                    } else 
                        if (strpos($s_name, 'Model_') !== false) {
                            $s_name = str_replace('Model_', '', $s_name);
                            $a_arguments[] = \core\Memory::models($s_name);
                        } else {
                            /* Try to load object */
                            $a_arguments[] = Loader::inject($s_name);
                        }
            } else {
                $a_arguments[] = Loader::inject($s_name);
            }
        }
        
        $a_arguments = array_merge($a_arguments, $a_argumentsGiven);
        
        $object = $ref->newInstanceArgs($a_arguments);
        
        if ($bo_singleton) {
            \core\Memory::setCache($s_caller, $object);
        }
        
        \Profiler::profileSystem('core/Loader.php', 'Loaded class ' . $s_caller);
        
        return $object;
    }

    /**
     * Gets the constructor parameters
     *
     * @param String $s_filename
     *            name
     * @return array parameters
     */
    private static function getConstructor($s_filename)
    {
        $service_File = \core\Memory::getCache(\core\IoC::$s_ruleFileHandler);
        
        if( $service_File->exists(str_replace('core/',CORE,$s_filename)) ){
            $s_file = $service_File->readFile(str_replace('core/',CORE,$s_filename));
        }
        else if ($service_File->exists($s_filename)) {
            $s_file = $service_File->readFile($s_filename);
        } else 
            if ($service_File->exists(str_replace('.inc.php', '.php', $s_filename))) {
                $s_file = $service_File->readFile(str_replace('.inc.php', '.php', $s_filename));
            } else {
                throw new Exception('Call to unknown file ' . $s_filename . '.');
            }
        
        if (stripos($s_file, '__construct') === false) {
            /* Check if file has parent */
            preg_match('#class\\s+[a-zA-Z0-9\-_]+\\s+extends\\s+([\\\a-zA-Z0-9_\-]+)#si', $s_file, $a_matches);
            if (count($a_matches) == 0) {
                return array();
            }
            
            switch ($a_matches[1]) {
                case '\core\models\Model':
                case 'Model':
                    $s_filename = NIV . 'core/models/Model.inc.php';
                    break;
                
                case '\core\services\Service':
                case 'Service':
                    $s_filename = NIV . 'core/services/Service.inc.php';
                    break;
                
                case '\core\helpers\Helper':
                case 'Helper':
                    $s_filename = NIV . 'core/helpers/Helper.inc.php';
                    break;
                
                default:
                    /* Check for namespace parent */
                    preg_match('#extends\\s+(\\\\{1}[\\\a-zA-Z0-9_\-]+)#si', $s_file, $a_matches2);
                    if (count($a_matches2) > 0) {
                        
                        if (strpos($a_matches2[1], '\core') !== false || strpos($a_matches2[1], '\includes') !== false || strpos($a_matches2[1], '\admin') !== false) {
                            $s_filename = NIV . $a_matches2[1] . '.inc.php';
                        } else 
                            if (file_exists(NIV . str_replace('\\', DS, $a_matches2[1]) . '.php')) {
                                $s_filename = NIV . $a_matches2[1] . '.php';
                            } 

                            else 
                                if (file_exists(NIV . str_replace('\\', DS, strtolower($a_matches2[1])) . '.php')) {
                                    $s_filename = NIV . strtolower($a_matches2[1]) . '.php';
                                } else {
                                    $s_filename = NIV . 'vendor' . DS . $a_matches2[1] . '.php';
                                }
                        $s_filename = str_replace(array(
                            '\\',
                            DS . DS
                        ), array(
                            DS,
                            DS
                        ), $s_filename);
                    } else {
                        /* Check for namespace */
                        preg_match('#namespace\\s+([\\a-z-_0-9]+);#', $s_file, $a_namespaces);
                        if (count($a_namespaces) > 0) {
                            $s_filename = NIV . str_replace('\\', '/', $a_namespaces[1] . '/' . $a_matches[1]) . '.inc.php';
                        } else {
                            $s_filename = NIV . str_replace('\\', '/', $a_matches[1]) . '.inc.php';
                        }
                    }
            }
            
            return Loader::getConstructor($s_filename);
        }
        
        preg_match('#function\\s+__construct\\s?\({1}\\s?([\\a-zA-Z\\s\$\-_,]+)\\s?\){1}#si', $s_file, $a_matches);
        
        return $a_matches;
    }
}

function loaderWrapper($s_className)
{
    Loader::autoload($s_className);
}

spl_autoload_register('loaderWrapper');