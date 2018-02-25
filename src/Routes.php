<?php

namespace youconix\Core;

final class Routes
{

  private $class = null;
  private $returnResult = null;
  private $config;
  private $exception;
  private $builder;
  private $controller;
  private $method;
  private $arguments;
  private $map;
  private $cacheFile;
  private $prettyUrls;
  private $fullUrl;

  /**
   *
   * @var \youconix\core\services\FileHandler
   */
  private $file;

  public function __construct(\Builder $builder, \Config $config,
                              \youconix\core\services\FileHandler $file)
  {
    $this->builder = $builder;
    $this->file = $file;

    $this->fullUrl = $config->getProtocol() . $config->getHost();
    $this->prettyUrls = $config->getPrettyUrls();

    $cacheDir = $config->getCacheDirectory();
    $this->cacheFile = $cacheDir . 'entityMap.php';

    $this->buildMap();
  }

  /**
   * Returns if the object should be treated as singleton
   *
   * @return boolean True if the object is a singleton
   */
  public static function isSingleton()
  {
    return true;
  }

  public function dropCache()
  {
    if ($this->file->exists($this->cacheFile)) {
      $this->file->deleteFile($this->cacheFile);
    }
  }

  public function buildMap()
  {
    if ($this->file->exists($this->cacheFile) && !defined('DEBUG')) {
      $content = $this->file->readFile($this->cacheFile);
      $this->map = unserialize($content);
      return;
    }

    $directoriesToSkip = [
      realpath(NIV . 'files'),
      realpath(NIV . 'vendor'),
      realpath(NIV . 'includes'),
      realpath(NIV . 'styles')
    ];

    $this->map = [];
    $names = $this->file->readFilteredDirectoryNames(NIV,
      $directoriesToSkip, 'php');
    foreach ($names as $directory) {
      $this->parseDirectory($directory);
    }

    if (!defined('DEBUG')) {
      $this->file->writeFile($this->cacheFile, serialize($this->map));
    }
  }

  /**
   *
   * @param string $name
   * @param array $parameters
   * @param boolean $fullUrl
   * @return string
   */
  public function path($name, array $parameters = [], $fullUrl = false)
  {
    $route = $this->getRouteByName($name, $parameters);

    if (!$this->prettyUrls) {
      $route = '/router.php' . $route;
    }

    if ($fullUrl) {
      $route = $this->fullUrl . $route;
    }

    return $route;
  }

  /**
   *
   * @param string $name
   * @param array $parameters
   * @return string
   * @throws \LogicException
   */
  public function getRouteByName($name, array $parameters)
  {
    if (!array_key_exists($name, $this->map)) {
      throw new \LogicException('Calling to unknown route name ' . $name . '.');
    }

    $route = $this->map[$name];
    if (!$route->regex) {
      return $route->route;
    }

    $missing = [];
    foreach ($route->parameters as $name => $value) {
      if (!array_key_exists($name, $parameters)) {
        $missing[] = $name;
      }
    }

    if (count($missing) > 0) {
      throw new \LogicException('Missing parameters for route ' . $name . ': ' . implode(',',
          $missing) . '.');
    }

    $route = $route->route_original;
    foreach ($parameters as $name => $value) {
      $route = str_replace('{' . $name . '}', $value, $route);
    }
    return $route;
  }

  /**
   * @return array
   */
  public function getAllAddresses()
  {
    $addresses = [];
    foreach ($this->map as $route) {
      $addresses[] = $route->route_original;
    }

    return $addresses;
  }

  /**
   *
   * @param array $directory
   */
  protected function parseDirectory($directory)
  {
    if (!is_array($directory)) {
      $this->parseFile($directory);
      return;
    }

    foreach ($directory as $item) {
      if (is_array($item)) {
        $this->parseDirectory($item);
      } else {
        $this->parseFile($item);
      }
    }
  }

  /**
   *
   * @param string $name
   */
  protected function parseFile($name)
  {
    $content = $this->file->readFile($name);
    $rules = preg_split("/\r\n|\n|\r/", $content);
    $matches = null;

    $nameSpace = '';
    if (preg_match('/namespace ([^\s^;]+)/s', $content, $matches)) {
      $nameSpace = $matches[1];
    }
    if (!empty($nameSpace)) {
      $nameSpace .= '\\';
    }

    if (!preg_match('/class\s+([^\s]+) extends/s', $content, $matches)) {
      return; //Not a controller
    }
    $class = '\\' . $nameSpace . $matches[1];
    $route = null;

    foreach ($rules as $rule) {
      if ((strpos($rule, '@Route') === false) && (strpos($rule, 'function') === false)) {
        continue;
      }
      if (preg_match('/@Route\("([^"]+)", name="([^"]+)"\)/s', $rule,
        $matches)) {
        $route = $this->createRoute($matches[1], $matches[2]);
      } else if (preg_match('/@Route\("([^"]+)", name="([^"]+)", requirements=\{([^}]+)\}\)/s',
        $rule, $matches)) {
        $parameters = explode(',', $matches[3]);
        $route = $this->createRoute($matches[1], $matches[2], $parameters);
      } else if (!is_null($route)) {
        preg_match('/function\s([a-zA-Z0-9\-_]+)\s?\(/s', $rule, $matches);
        $route->function = $matches[1];
        $route->class = $class;

        if (array_key_exists($route->name, $this->map)) {
          throw new \CoreException('Found duplicate route name ' . $route->name . ' pointing to route ' . $route->route_original . '.');
        }

        $this->map[$route->name] = $route;

        $route = null;
      }
    }
  }

  /**
   *
   * @param string $route
   * @param string $name
   * @param array $parameters
   * @return \stdClass
   */
  private function createRoute($route, $name, array $parameters = [])
  {
    $regex = (strpos($route, '{') !== false);

    $route = new \stdClass();
    $route->route_original = $route;
    $route->regex = $regex;
    $route->name = $name;
    $route->parameters = [];

    for ($i = 0; $i < count($parameters); $i++) {
      $parts = explode(':', $parameters[$i]);
      $variable = str_replace('"', '', str_replace("'", '', $parts[0]));
      $value = str_replace('"', '', str_replace("'", '', $parts[1]));

      $route = str_replace('{' . $variable . '}', '(' . $value . ')',
        $route);
      $route->parameters[$variable] = $value;
    }

    $index = explode('{', $route->route_original);

    $matches = null;
    if (preg_match_all('/{([^}]+)}/s', $route, $matches)) {
      $replace = '[^/]+';
      for ($i = 0; $i < count($matches[1]); $i++) {
        $route = str_replace('{' . $matches[1][$i] . '}', '(' . $replace . ')',
          $route);
        $route->parameters[$matches[1][$i]] = $replace;
      }
    }
    if ($regex) {
      $route = str_replace('/', '\/', $route);
    }

    $route->route = $route;
    $route->index = $index[0];

    return $route;
  }

  public function clearException()
  {
    $this->exception = null;
  }

  /**
   *
   * @param \Exception $exception
   */
  public function setException(\Exception $exception)
  {
    $this->exception = $exception;
  }

  /**
   *
   * @param \Config $config
   * @return \youconix\core\templating\BaseController
   * @throws \Http404Exception
   */
  public function findController(\Config $config)
  {
    $this->config = $config;

    $address = str_replace('router.php', '', $_SERVER['PHP_SELF']);
    while (substr($address, 0, 1) == '/') {
      $address = substr($address, 1);
    }

    if ($address == '/') {
      $address = '/index/view';
    }

    if (substr($address, 0, 1) != '/') {
      $address = '/' . $address;
    }

    $this->controller = '';
    foreach ($this->map as $route) {
      if (!$route->regex && $address == $route->route) {
        $this->controller = $route->class;
        $this->method = $route->function;
        break;
      } else if ($route->regex && stripos($address, $route->index) !== false && preg_match('/^' . $route->route . '$/s',
          $address, $this->arguments)) {
        unset($this->arguments[0]);
        $this->controller = $route->class;
        $this->method = $route->function;
        break;
      }
    }

    if (empty($this->controller)) {
      throw new \Http404Exception('Unknown page.');
    }

    if ($this->checkController($address)) {
      return $this->class;
    }

    throw new \Http404Exception('Unknown page.');
  }

  /**
   *
   * @return boolean
   * @param string $address
   * @throws \RuntimeException
   */
  private function checkController($address)
  {
    $file = str_replace('\\', DS, $this->controller);
    if (!file_exists(WEB_ROOT . $file . '.php')) {
      $file = preg_replace_callback('/([A-Z])/s',
        function ($route) {
          return '_' . strtolower($route[1]);
        }, $file);
      $file = str_replace('/_', '/', $file);

      if (!file_exists(WEB_ROOT . $file . '.php')) {
        return false;
      }
    }

    $caller = str_replace('/', '\\', $this->controller);

    $_SERVER['SCRIPT_NAME'] = $this->controller . '/' . $this->method;

    $reflector = new \ReflectionClass($caller);
    if (!$reflector->hasMethod($this->method)) {
      return false;
    }
    if (!$reflector->getMethod($this->method)->isPublic()) {
      throw new \RuntimeException('Can not call method ' . $this->method . ' from class ' . $caller . '. Method is not public.');
    }

    $class = \Loader::inject($caller);
    if (!method_exists($class, $this->method)) {
      $class = null;
      return false;
    }

    $this->config->setCall($file, $address, $this->controller,
      $this->method);

    Routes::checkLogin();

    $this->returnResult = null;
    $this->class = null;

    if (count($this->arguments) === 0) {
      $this->returnResult = call_user_func([$class, $this->method]);
    } else {
      $this->returnResult = call_user_func_array([$class, $this->method],
        $this->arguments);
    }

    $this->class = $class;

    return true;
  }

  private function checkLogin()
  {
    \Loader::inject('\youconix\core\models\Privileges')->checkLogin();
  }

  /**
   *
   * @return \Output
   */
  public function getResult()
  {
    return $this->returnResult;
  }
}
