<?php

namespace youconix\core;

final class Routes
{

  private $class = null;
  private $returnResult = null;
  private $config;
  private $exception;
  private $builder;
  private $s_controller;
  private $s_method;
  private $a_arguments;
  private $a_map;
  private $s_cacheFile;
  private $bo_prettyUrls;
  private $s_fullUrl;

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

    $this->s_fullUrl = $config->getProtocol() . $config->getHost();
    $this->bo_prettyUrls = $config->getPrettyUrls();

    $s_cacheDir = $config->getCacheDirectory();
    $this->s_cacheFile = $s_cacheDir . 'entityMap.php';

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
    if ($this->file->exists($this->s_cacheFile)) {
      $this->file->deleteFile($this->s_cacheFile);
    }
  }

  public function buildMap()
  {
    if ($this->file->exists($this->s_cacheFile) && !defined('DEBUG')) {
      $s_content = $this->file->readFile($this->s_cacheFile);
      $this->a_map = unserialize($s_content);
      return;
    }

    $a_directoriesToSkip = [
	realpath(NIV . 'files'),
	realpath(NIV . 'vendor'),
	realpath(NIV . 'includes'),
	realpath(NIV . 'styles')
    ];

    $this->a_map = [];
    $a_names = $this->file->readFilteredDirectoryNames(NIV,
						       $a_directoriesToSkip, 'php');
    foreach ($a_names as $a_directory) {
      $this->parseDirectory($a_directory);
    }

    if (!defined('DEBUG')) {
      $this->file->writeFile($this->s_cacheFile, serialize($this->a_map));
    }
  }

  /**
   * 
   * @param string $s_name
   * @param array $a_parameters
   * @param boolean $bo_fullUrl
   * @return string
   */
  public function path($s_name, array $a_parameters = [], $bo_fullUrl = false)
  {
    $s_route = $this->getRouteByName($s_name, $a_parameters);

    if (!$this->bo_prettyUrls) {
      $s_route = '/router.php' . $s_route;
    }

    if ($bo_fullUrl) {
      $s_route = $this->s_fullUrl . $s_route;
    }

    return $s_route;
  }

  /**
   * 
   * @param string $s_name
   * @param array $a_parameters
   * @return string
   * @throws \LogicException
   */
  public function getRouteByName($s_name, array $a_parameters)
  {
    if (!array_key_exists($s_name, $this->a_map)) {
      throw new \LogicException('Calling to unknown route name ' . $s_name . '.');
    }

    $route = $this->a_map[$s_name];
    if (!$route->regex) {
      return $route->route;
    }

    $a_missing = [];
    foreach ($route->parameters as $name => $value) {
      if (!array_key_exists($name, $a_parameters)) {
	$a_missing[] = $name;
      }
    }

    if (count($a_missing) > 0) {
      throw new \LogicException('Missing parameters for route ' . $s_name . ': ' . implode(',',
											   $a_missing) . '.');
    }

    $s_route = $route->route_original;
    foreach ($a_parameters as $name => $value) {
      $s_route = str_replace('{' . $name . '}', $value, $s_route);
    }
    return $s_route;
  }

  public function getAllAddresses()
  {
    $addresses = [];
    foreach ($this->a_map as $route) {
      $addresses[] = $route->route_original;
    }

    return $addresses;
  }

  /**
   * 
   * @param array $a_directory
   */
  protected function parseDirectory($a_directory)
  {
    if (!is_array($a_directory)) {
      $this->parseFile($a_directory);
      return;
    }

    foreach ($a_directory as $item) {
      if (is_array($item)) {
	$this->parseDirectory($item);
      } else {
	$this->parseFile($item);
      }
    }
  }

  /**
   * 
   * @param string $s_name
   */
  protected function parseFile($s_name)
  {
    $s_content = $this->file->readFile($s_name);
    $a_rules = preg_split("/\r\n|\n|\r/", $s_content);
    $a_matches = null;

    $s_nameSpace = '';
    if (preg_match('/namespace ([^\s^;]+)/s', $s_content, $a_matches)) {
      $s_nameSpace = $a_matches[1];
    }
    if (!empty($s_nameSpace)) {
      $s_nameSpace .= '\\';
    }

    if (!preg_match('/class\s+([^\s]+) extends/s', $s_content, $a_matches)) {
      return; //Not a controller
    }
    $s_class = '\\' . $s_nameSpace . $a_matches[1];
    $route = null;

    foreach ($a_rules as $s_rule) {
      if ((strpos($s_rule, '@Route') === false) && (strpos($s_rule, 'function') === false)) {
	continue;
      }
      if (preg_match('/@Route\("([^"]+)", name="([^"]+)"\)/s', $s_rule,
		     $a_matches)) {
	$route = $this->createRoute($a_matches[1], $a_matches[2]);
      } else if (preg_match('/@Route\("([^"]+)", name="([^"]+)", requirements=\{([^}]+)\}\)/s',
			    $s_rule, $a_matches)) {
	$a_parameters = explode(',', $a_matches[3]);
	$route = $this->createRoute($a_matches[1], $a_matches[2], $a_parameters);
      } else if (!is_null($route)) {
	preg_match('/function\s([a-zA-Z0-9\-_]+)\s?\(/s', $s_rule, $a_matches);
	$route->function = $a_matches[1];
	$route->class = $s_class;

	if (array_key_exists($route->name, $this->a_map)) {
	  throw new \CoreException('Found duplicate route name ' . $route->name . ' pointing to route ' . $route->route_original . '.');
	}

	$this->a_map[$route->name] = $route;

	$route = null;
      }
    }
  }

  /**
   * 
   * @param string $s_route
   * @param string $s_name
   * @param array $a_parameters
   * @return \stdClass
   */
  private function createRoute($s_route, $s_name, array $a_parameters = [])
  {
    $bo_regex = (strpos($s_route, '{') !== false);

    $route = new \stdClass();
    $route->route_original = $s_route;
    $route->regex = $bo_regex;
    $route->name = $s_name;
    $route->parameters = [];

    for ($i = 0; $i < count($a_parameters); $i++) {
      $a_parts = explode(':', $a_parameters[$i]);
      $s_variable = str_replace('"', '', str_replace("'", '', $a_parts[0]));
      $s_value = str_replace('"', '', str_replace("'", '', $a_parts[1]));

      $s_route = str_replace('{' . $s_variable . '}', '(' . $s_value . ')',
			     $s_route);
      $route->parameters[$s_variable] = $s_value;
    }

    $a_index = explode('{', $route->route_original);

    $a_matches = null;
    if (preg_match_all('/{([^}]+)}/s', $s_route, $a_matches)) {
      $s_replace = '[^/]+';
      for ($i = 0; $i < count($a_matches[1]); $i++) {
	$s_route = str_replace('{' . $a_matches[1][$i] . '}', '(' . $s_replace . ')',
			$s_route);
	$route->parameters[$a_matches[1][$i]] = $s_replace;
      }
    }
    if ($bo_regex) {
      $s_route = str_replace('/', '\/', $s_route);
    }

    $route->route = $s_route;
    $route->index = $a_index[0];

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

    $s_address = str_replace('router.php', '', $_SERVER['PHP_SELF']);
    while (substr($s_address, 0, 1) == '/') {
      $s_address = substr($s_address, 1);
    }

    if ($s_address == '/') {
      $s_address = '/index/view';
    }

    if (substr($s_address, 0, 1) != '/') {
      $s_address = '/' . $s_address;
    }

    $this->s_controller = '';
    foreach ($this->a_map as $route) {
      if (!$route->regex && $s_address == $route->route) {
	$this->s_controller = $route->class;
	$this->s_method = $route->function;
	break;
      } else if ($route->regex && stripos($s_address, $route->index) !== false && preg_match('/^' . $route->route . '$/s',
											     $s_address, $this->a_arguments)) {
	unset($this->a_arguments[0]);
	$this->s_controller = $route->class;
	$this->s_method = $route->function;
	break;
      }
    }

    if (empty($this->s_controller)) {
      throw new \Http404Exception('Unknown page.');
    }

    if ($this->checkController($s_address)) {
      return $this->class;
    }

    throw new \Http404Exception('Unknown page.');
  }

  /**
   * 
   * @return boolean
   * @param string $s_address
   * @throws \RuntimeException
   */
  private function checkController($s_address)
  {
    $s_file = str_replace('\\', DS, $this->s_controller);
    if (!file_exists(WEB_ROOT . $s_file . '.php')) {
      $s_file = preg_replace_callback('/([A-Z])/s',
				      function($route) {
	return '_' . strtolower($route[1]);
      }, $s_file);
      $s_file = str_replace('/_', '/', $s_file);

      if (!file_exists(WEB_ROOT . $s_file . '.php')) {
	return false;
      }
    }

    $s_caller = str_replace('/', '\\', $this->s_controller);

    $_SERVER['SCRIPT_NAME'] = $this->s_controller . '/' . $this->s_method;

    $reflector = new \ReflectionClass($s_caller);
    if (!$reflector->hasMethod($this->s_method)) {
      return false;
    }
    if (!$reflector->getMethod($this->s_method)->isPublic()) {
      throw new \RuntimeException('Can not call method ' . $this->s_method . ' from class ' . $s_caller . '. Method is not public.');
    }

    $class = \Loader::inject($s_caller);
    if (!method_exists($class, $this->s_method)) {
      $class = null;
      return false;
    }

    $this->config->setCall($s_file, $s_address, $this->s_controller,
			   $this->s_method);

    Routes::checkLogin();

    $this->returnResult = null;
    $this->class = null;

    if (count($this->a_arguments) === 0) {
      $this->returnResult = call_user_func([$class, $this->s_method]);
    } else {
      $this->returnResult = call_user_func_array([$class, $this->s_method],
						 $this->a_arguments);
    }

    $this->class = $class;

    return true;
  }

  private function checkLogin()
  {
    \Profiler::profileSystem('core/models/Privileges', 'Checking access level');
    \Loader::inject('\youconix\core\models\Privileges')->checkLogin();
    \Profiler::profileSystem('core/models/Privileges',
			     'Checking access level completed');
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
