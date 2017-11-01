<?php

namespace youconix\core;

class Routes {

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

  /**
   *
   * @var \youconix\core\services\FileHandler
   */
  private $file;

  public function __construct(\Builder $builder, \Config $config, \youconix\core\services\FileHandler $file) {
    $this->builder = $builder;
    $this->file = $file;

    $s_cacheDir = $config->getCacheDirectory();
    $this->s_cacheFile = $s_cacheDir . 'entityMap.php';

    $this->buildMap();
  }

  /**
   * Returns if the object should be treated as singleton
   *
   * @return boolean True if the object is a singleton
   */
  public static function isSingleton() {
    return true;
  }

  public function buildMap() {
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
    $a_names = $this->file->readFilteredDirectoryNames(NIV, $a_directoriesToSkip, 'php');
    foreach ($a_names as $a_directory) {
      $this->parseDirectory($a_directory);
    }
    
    if (!defined('DEBUG')) {
      $this->file->writeFile($this->s_cacheFile, serialize($this->a_map));
    }
  }

  /**
   * 
   * @param array $a_directory
   */
  protected function parseDirectory($a_directory) {
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
  protected function parseFile($s_name) {
    $s_content = $this->file->readFile($s_name);
    $a_rules = preg_split("/\r\n|\n|\r/", $s_content);
    $a_matches = null;

    $s_nameSpace = '';
    if (preg_match('/namespace ([a-zA-Z0-9\-_\/]+)/s', $s_content, $a_matches)) {
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
      if (preg_match('/@Route\("([a-zA-Z0-9_\-\/]+)"\)/s', $s_rule, $a_matches)) {
        $route = new \stdClass();
        $route->route = $a_matches[1];
        $route->index = $route->route;
        $route->regex = false;
      } else if (preg_match('/@Route\("([a-zA-Z0-9_\-\/\{\}]+)"/s', $s_rule, $a_matches)) {
        $s_route = $a_matches[1];
        $a_parameters = null;
        preg_match_all('/([^=^\s^,]+="[^"]+")/s', $s_rule, $a_parameters);

        for ($i = 0; $i < count($a_parameters[0]); $i++) {
          $a_parts = explode('=', $a_parameters[0][$i]);
          $s_variable = trim($a_parts[0]);
          $s_value = str_replace('"', '', str_replace("'", '', $a_parts[1]));

          $s_route = str_replace('{' . $s_variable . '}', '(' . $s_value . ')', $s_route);
        }

        $a_index = explode('{', $a_matches[1]);

        $s_route = preg_replace('/{[^}]+}/s', '([^/]+)', str_replace('/', '\/', $s_route));

        $route = new \stdClass();
        $route->route = $s_route;
        $route->index = $a_index[0];
        $route->regex = true;
      } else if (!is_null($route)) {
        preg_match('/function\s([a-zA-Z0-9\-_]+)\s?\(/s', $s_rule, $a_matches);
        $route->function = $a_matches[1];
        $route->class = $s_class;
        $this->a_map[] = $route;

        $route = null;
      }
    }
  }

  public function clearException() {
    $this->exception = null;
  }

  /**
   * 
   * @param \Exception $exception
   */
  public function setException(\Exception $exception) {
    $this->exception = $exception;
  }

  /**
   * 
   * @param \Config $config
   * @return \youconix\core\templating\BaseController
   * @throws \Http404Exception
   */
  public function findController(\Config $config) {
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
      } else if ($route->regex && stripos($s_address, $route->index) !== false && preg_match('/' . $route->route . '/s', $s_address, $this->a_arguments)) {
        unset($this->a_arguments[0]);
        $this->s_controller = $route->class;
        $this->s_method = $route->function;
        break;
      }
    }

    if (empty($this->s_controller)) {
      die('Page not found');
      throw new \Http404Exception('Unknown page.');
    }

    if ($this->checkController()) {
      return $this->class;
    }

    throw new \Http404Exception('Unknown page.');
  }

  /**
   * 
   * @return boolean
   * @throws \RuntimeException
   */
  private function checkController() {
    $s_file = str_replace('\\', DS, $this->s_controller);
    if (!file_exists(WEB_ROOT . $s_file . '.php')) {
      $s_file = strtolower($s_file);
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

    $this->config->setCall($this->s_controller, $this->s_method);

    Routes::checkLogin();

    $this->returnResult = null;
    $this->class = null;

    if (count($this->a_arguments) === 0) {
      $this->returnResult = call_user_func([$class, $this->s_method]);
    } else {
      $this->returnResult = call_user_func_array([$class, $this->s_method], $this->a_arguments);
    }

    $this->class = $class;

    return true;
  }

  private function checkLogin() {
    \Profiler::profileSystem('core/models/Privileges', 'Checking access level');
    \Loader::inject('\youconix\core\models\Privileges')->checkLogin();
    \Profiler::profileSystem('core/models/Privileges', 'Checking access level completed');
  }

  /**
   * 
   * @return \Output
   */
  public function getResult() {
    return $this->returnResult;
  }

}
