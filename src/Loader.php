<?php

use youconix\Core\Memory;

/**
 * General class loader and dependency injection
 *
 * @link        http://www.php-fig.org/psr/psr-4/
 */
final class Loader
{

    private static $uses = [];

  /**
   * @param string $className
   * @return null|string
   */
    private static function getFileName($className)
    {
        /* Check for Interfaces */
        if (file_exists(WEB_ROOT . CORE . 'Interfaces' . DS . $className . '.php')) {
            return CORE . 'Interfaces' . DS . $className . '.php';
        }
        if (file_exists(WEB_ROOT . 'includes' . DS . 'Interfaces' . DS . $className . '.php')) {
            return 'Includes' . DS . 'Interfaces' . DS . $className . '.php';
        }

        $className = ltrim($className, '\\');
        $fileName = '';
        if ($lastNsPos = strrpos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName = str_replace('\\', DS, $namespace) . DS;
            $fileName = str_replace('youconix/Core/', 'youconix/Core/src/', $fileName);
        }

        if (file_exists(WEB_ROOT . 'vendor' . DS . $fileName . DS . $className . '.php')) {
          return 'vendor' . DS . $fileName . DS . $className . '.php';
        }

        if (file_exists(WEB_ROOT . 'vendor' . DS . strtolower($fileName) . DS . $fileName . DS . $className . '.php')) {
            return 'vendor' . DS . strtolower($fileName) . DS . $fileName . DS . $className . '.php';
        }

        if (file_exists(WEB_ROOT . $fileName . DS . $className . '.inc.php')) {
            return $fileName . DS . $className . '.inc.php';
        }

        if (file_exists(WEB_ROOT . $fileName . DS . $className . '.php')) {
            return $fileName . DS . $className . '.php';
        }

        /* Check for website files */
        $className = preg_replace_callback('/([A-Z])/s',
            function ($route) {
                return '_' . strtolower($route[1]);
            }, lcfirst($className));
        $name = strtolower($fileName . DS . $className . '.php');
        if (file_exists(WEB_ROOT . $name)) {
            return $name;
        }
        if (defined('WEBSITE_ROOT')) {
            if (file_exists(WEB_ROOT . $name . '.php')) {
                return $name . '.php';
            }
        }

        return null;
    }

  /**
   * @param string $className
   */
    public static function autoload($className)
    {
        if (preg_match('/Exception$/', $className)) {
            $fileName = null;
            if (file_exists(WEB_ROOT . DS . 'vendor' . DS . 'youconix' . DS . 'Core' . DS . 'src'.DS.'Exceptions' . DS . $className . '.php')) {
                $fileName = 'vendor' . DS . 'youconix' . DS . 'Core' . DS . 'src'.DS.'Exceptions' . DS . $className . '.php';
            }
        } else {
            $fileName = self::getFileName($className);
        }

        if (!is_null($fileName)) {
          require WEB_ROOT . $fileName;
        }
    }

  /**
   * @param string $className
   * @param array $arguments
   * @return null|Object
   */
    public static function inject($className, array $arguments = [])
    {
        $fileName = null;
        /* Check IoC */
        $IoC = Memory::getCache('IoC');
        if (!is_null($IoC)) {
            $check = $IoC::check($className);
            if (!is_null($check)) {
                $className = $check;
                $fileName = str_replace('\\', DS, $check) . '.php';
                $fileName = str_replace('youconix/Core/', 'youconix/Core/src/', $fileName);

                while (substr($fileName, 0, 1) == DS) {
                    $fileName = substr($fileName, 1);
                }

                if (!file_exists(WEB_ROOT . DS . $fileName)) {
                    if (file_exists(WEB_ROOT . DS . 'vendor' . DS . $fileName)) {
                        $fileName = 'vendor' . DS . $fileName;
                    } else {
                        throw new \LogicException('IoC defined file ' . $fileName . ' does not exist.');
                    }
                }
            }
        }

        if (is_null($fileName)) {
            $fileName = self::getFileName($className);
        }

        if (is_null($fileName)) {
            return null;
        }

        $caller = $className;

        if (substr($caller, 0, 1) != '\\') {
            $caller = '\\' . $caller;
        }

        if (!class_exists($caller, false) && !interface_exists($caller, false)) {
            require(WEB_ROOT . $fileName);
        }
        if ((substr($fileName, 0, 5) == 'Core/') && (file_exists(WEB_ROOT . str_replace('Core/',
                        'includes/', $fileName) . '.inc.php') || file_exists(WEB_ROOT . str_replace('Core/',
                        'includes/', $fileName) . '.php'))) {
            $fileName = str_replace('Core\\', 'includes\\', $fileName);
            $caller = str_replace('\youconix\core', '\includes', $caller);

            if (!class_exists($caller, false)) {
                require(WEB_ROOT . $fileName);
            }
        }

        $object = self::injection($caller, $fileName, $arguments);

        return $object;
    }

    /**
     * Performs the dependency injection
     *
     * @param string $caller
     * @param string $filename
     * @param array $argumentsGiven
     * @throws RuntimeException the object is not instantiable.
     * @return Object called object
     */
    private static function injection($caller, $filename, array $argumentsGiven)
    {
        $ref = new \ReflectionClass($caller);
        if (!$ref->isInstantiable()) {
            /* Check cache */
            if (Memory::IsInCache($caller)) {
                return Memory::getCache($caller);
            }

            throw new \RuntimeException('Can not create a object from class ' . $caller . '.');
        }

        $singleton = false;
        if (method_exists($caller, 'isSingleton') && $caller::isSingleton()) {
            /* Check cache */
            if (Memory::IsInCache($caller)) {
                return Memory::getCache($caller);
            } else {
                $singleton = true;
            }
        }

        $matches = self::getConstructor($filename);

        if (count($matches) == 0) {
            /* No arguments */
            return new $caller();
        }
        $argumentNamesPre = explode(',', $matches[1]);

        $argumentNames = [];
        $arguments = [];

        foreach ($argumentNamesPre as $name) {
            $name = trim($name);
            if (strpos($name, ' ') === false) {
                continue;
            }
            if (substr($name, 0, 1) == '\\') {
                $name = substr($name, 1);
            }

            $item = explode(' ', $name);
            if (array_key_exists($item[0], self::$uses)) {
                $item[0] = self::$uses[$item[0]];
            }

            $argumentNames[] = $item[0];
        }

        foreach ($argumentNames as $name) {
            $path = explode('\\', $name);

            if (count($path) == 1) {
                /* No namespace */
                if (strpos($name, 'Helper_') !== false) {
                    $name = str_replace('Helper_', '', $name);
                    $arguments[] = Memory::helpers($name);
                } else
                    if (strpos($name, 'Service_') !== false) {
                        $name = str_replace('Service_', '', $name);
                        $arguments[] = Memory::services($name);
                    } else
                        if (strpos($name, 'Model_') !== false) {
                            $name = str_replace('Model_', '', $name);
                            $arguments[] = Memory::models($name);
                        } else {
                            /* Try to load object */
                            $arguments[] = self::inject($name);
                        }
            } else {
                $arguments[] = self::inject($name);
            }
        }

        $arguments = array_merge($arguments, $argumentsGiven);

        $object = $ref->newInstanceArgs($arguments);

        if ($singleton) {
            Memory::setCache($caller, $object);
        }

        return $object;
    }

    /**
     * Gets the constructor parameters
     *
     * @param string $filename
     * @return array parameters
     */
    private static function getConstructor($filename)
    {
        if (substr($filename, 0, 8) == 'youconix') {
            $filename = 'vendor' . DS . $filename;
        }

        if ( strpos($filename, 'vendor/youconix') !== false && strpos($filename,'Core/src') === false) {
          $filename = str_replace('youconix/Core/', 'youconix/Core/src/', $filename);
        }

        $fileHandler = Memory::getCache(\youconix\core\IoC::$ruleFileHandler);

        if ($fileHandler->exists(WEB_ROOT . DS . $filename)) {
            $file = $fileHandler->readFile(WEB_ROOT . DS . $filename);
        } elseif ($fileHandler->exists($filename)) {
            $file = $fileHandler->readFile($filename);
        } elseif ($fileHandler->exists(str_replace('.inc.php', '.php', $filename))) {
            $file = $fileHandler->readFile(str_replace('.inc.php', '.php',
                $filename));
        } else {
            throw new \Exception('Call to unknown file ' . $filename . '.');
        }

        // Find use statements
        self::findUses($file);

        if (stripos($file, '__construct') === false) {
            /* Check if file has parent */
            preg_match('#class\\s+[a-z0-9\-_]+\\s+extends\\s+([\\\a-z0-9_\-]+)#si',
                $file, $matches);
            if (count($matches) == 0) {
                return array();
            }

            if (array_key_exists($matches[1], self::$uses)) {
                $matches[1] = self::$uses[$matches[1]];
            }

            switch ($matches[1]) {
                case '\youconix\Core\Models\Model':
                case 'Model':
                    $filename = 'vendor' . DS . 'youconix' . DS . 'Core' . DS . 'src'.DS.'Models' . DS . 'AbstractModel.php';
                    break;

                case '\youconix\Core\Services\AbstractService':
                case 'Service':
                    $filename = 'vendor' . DS . 'youconix' . DS . 'Core' . DS . 'src'.DS.'Services' . DS . 'AbstractService.php';
                    break;

                case '\youconix\Core\Helpers\Helper':
                case 'Helper':
                    $filename = 'vendor' . DS . 'youconix' . DS . 'Core' . DS . 'src'.DS.'Helpers' . DS . 'AbstractHelper.php';
                    break;

                default:
                    /* Check for namespace parent */
                    preg_match('#extends\\s+(\\\\{1}[\\\a-zA-Z0-9_\-]+)#si', $file, $matches2);
                    if (count($matches2) > 0) {
                        $filename = self::parentNamespace($matches2);
                    } else {
                        /* Check for namespace */
                        preg_match('#namespace\\s+([\\a-z-_0-9]+);#', $file, $a_namespaces);
                        if (count($a_namespaces) > 0) {
                            $filename = str_replace('\\', '/',
                                    $a_namespaces[1] . '/' . $matches[1]) . '.php';
                        } else {
                            $filename = str_replace('\\', '/', $matches[1]) . '.php';
                        }
                    }
            }

            return self::getConstructor($filename);
        }

        preg_match('#function\\s+__construct\\s?\({1}\\s?([\\a-zA-Z\\s\$\-_,]+)\){1}#si',
            $file, $matches);

        return $matches;
    }

    /**
     * @param string $file
     */
    private static function findUses($file)
    {
        $uses = [];
        self::$uses = array();

        preg_match_all('#use\\s+([\\\a-z0-9\-_]+\\s+as\\s+[\\\a-z0-9\-_]+);#si',
            $file, $uses);

        foreach ($uses AS $use) {
            foreach ($use AS $line) {
                if (substr($line, 0, 3) == 'use') {
                    continue;
                }

                $line = str_replace(' AS ', ' as ', $line);
                $parts = explode(' as ', $line);

                self::$uses[trim($parts[1])] = trim($parts[0]);
            }
        }

        preg_match_all('#use\\s+([\\\a-z0-9\-_]+);#si', $file, $uses);

        foreach ($uses AS $use) {
            foreach ($use AS $line) {
                if (substr($line, 0, 3) == 'use') {
                    continue;
                }

                $parts = explode('\\', $line);
                $className = end($parts);

                self::$uses[trim($className)] = '\\'.$line;
            }
        }
    }

    /**
     *
     * @param array $matches
     * @return string
     */
    private static function parentNamespace(array $matches)
    {
        if (file_exists(WEB_ROOT . DS . 'vendor' . $matches[1] . '.php')) {
            $filename = 'vendor' . DS . $matches[1] . '.php';
        } elseif (file_exists(WEB_ROOT . str_replace('\\', DS, $matches[1]) . '.php')) {
            $filename = $matches[1] . '.php';
        } elseif (file_exists(WEB_ROOT . str_replace('\\', DS, $matches[1]) . '.inc.php')) {
            $filename = $matches[1] . '.inc.php';
        } elseif (file_exists(WEB_ROOT . str_replace('\\', DS,
                strtolower($matches[1])) . '.php')) {
            $filename = strtolower($matches) . '.php';
        } else {
            $filename = 'vendor' . DS . $matches[1] . '.php';
        }
        $filename = str_replace(array(
            '\\',
            DS . DS
        ),
            array(
                DS,
                DS
            ), $filename);
        return $filename;
    }
}

/**
 * @param string $className
 */
function loaderWrapper($className)
{
    Loader::autoload($className);
}

spl_autoload_register('loaderWrapper');
