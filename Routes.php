<?php

namespace youconix\core;

class Route {

  private $s_method;
  private $s_path;
  private $a_conditions = [];
  private $s_controller;
  private $s_function;
  private $a_arguments = [];

  public function __construct($s_method, $s_path, $s_controller, $s_function) {
    $this->s_method = $s_method;
    $this->s_path = $s_path;
    $this->s_controller = $s_controller;
    $this->s_function = $s_function;
  }

  public function where($s_field, $s_regex) {
    $this->a_conditions[$s_field] = $s_regex;

    return $this;
  }

  private function render() {
    $s_regex = $this->s_path;
    foreach ($this->a_conditions AS $s_key => $s_condition) {
      $s_regex = str_replace('{' . $s_key . '}', '(' . $s_condition . ')', $s_regex);
    }

    $s_regex = preg_replace('/{[a-zA-Z]+}/', '(.+)', $s_regex);

    return '/^' . str_replace('/', '\/', $s_regex) . '$/si';
  }

  public function isValid($s_address) {
    /* Precheck */
    $s_partAddress = substr($s_address, 0, strpos($s_address, '/', 1));
    $s_partPath = substr($this->s_path, 0, strpos($this->s_path, '/', 1));

    if ($s_partAddress != $s_partPath) {
      return false;
    }

    $s_regex = $this->render();
    $a_matches = [];
    preg_match_all($s_regex, $s_address, $a_matches);
    if (count($a_matches[0]) == 0) {
      return false;
    }

    $this->a_arguments = [];
    for ($i = 1; $i < count($a_matches); $i++) {
      $this->a_arguments[] = $a_matches[$i];
    }

    return true;
  }

  public function getController() {
    return $this->s_controller;
  }

  public function getMethod() {
    return $this->s_function;
  }

  public function getArguments() {
    return $this->a_arguments;
  }

}

class Routes {
  private static $class = null;

  /**
   *
   * @var Route[]
   */
  private static $a_routes = [];

  public static function get($s_path, $s_controller, $s_method) {
    return Routes::addRoute('get', $s_path, $s_controller, $s_method);
  }

  public static function post($s_path, $s_controller, $s_method) {
    return Routes::addRoute('post', $s_path, $s_controller, $s_method);
  }

  public static function put($s_path, $s_controller, $s_method) {
    return Routes::addRoute('post', $s_path, $s_controller, $s_method);
  }

  public static function delete($s_path, $s_controller, $s_method) {
    return Routes::addRoute('post', $s_path, $s_controller, $s_method);
  }

  public static function any($s_path, $s_controller, $s_method) {
    return Routes::addRoute('any', $s_path, $s_controller, $s_method);
  }

  private static function addRoute($s_connectionMethod, $s_path, $s_controller, $s_method) {
    $route = new Route($s_connectionMethod, $s_path, $s_controller, $s_method);
    Routes::$a_routes[] = $route;

    return $route;
  }

  public static function findController() {
    $s_address = str_replace('router.php','',$_SERVER['PHP_SELF']);
    while(substr($s_address,0,1) == '/'){
      $s_address = substr($s_address,1);
    }
    
    if ($s_address == '/') {
      $s_address = '/index/view';
    }

    foreach (Routes::$a_routes AS $route) {
      if (!$route->isValid($s_address)) {
	continue;
      }

      $s_controller = $route->getController();
      $s_method = $route->getMethod();
      $a_arguments = $route->getArguments();
      if( !Routes::checkController($s_controller, $s_method, $a_arguments) ){
	continue;
      }
      
      return Routes::$class;
    }

    // No predefined route found
    $pos = strrpos($s_address, '/');
    $s_controller = substr($s_address, 0, $pos);
    $s_method = substr($s_address, ($pos + 1));
    if( Routes::checkController($s_controller, $s_method) ){
      return Routes::$class;
    }

    throw new \InvalidArgumentException('Unknown page.');
  }

  private static function checkController($s_controller,$s_method,$a_arguments = []){
      if (!file_exists(WEB_ROOT . $s_controller.'.php')) {
	return false;
      }

      $s_caller = str_replace('/', '\\', $s_controller);

      $_SERVER['SCRIPT_NAME'] = $s_controller.'/'.$s_method;
      
      $reflector = new \ReflectionClass($s_caller);
      if( !$reflector->hasMethod($s_method) ){
	return false;
      }
      if( !$reflector->getMethod($s_method)->isPublic() ){
	throw new \RuntimeException('Can not call method '.$s_method.' from class '.$s_caller.'. Method is not public.');
      }
      
      $class = \Loader::inject($s_caller);
      if (!method_exists($class, $s_method)) {
	$class = null;
	return false;
      }

      if( count($a_arguments) === 0 ){
	call_user_func([$class,$s_method]);
      }
      else {
	call_user_func_array([$class, $s_method], $a_arguments);
      }      
      Routes::$class = $class;
      return true;
  }
}
