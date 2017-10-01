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

  public function __construct(\Builder $builder){
    $this->builder = $builder;
  }
  
  public function clearException(){
    $this->exception = null;
  }
  
  public function setException(\Exception $exception){
    $this->exception = $exception;
  }

  public function findController(\Config $config) {
    $this->config = $config;
    
    $s_address = str_replace('router.php','',$_SERVER['PHP_SELF']);
    while(substr($s_address,0,1) == '/'){
      $s_address = substr($s_address,1);
    }
    
    if ($s_address == '/') {
      $s_address = '/index/view';
    }

    if( $this->checkPredefinedRoute($s_address) ){
      if( !is_null($this->exception) ){
	$this->a_arguments[] = $this->exception;
      }
      if( $this->checkController()){
	return $this->class;
      }
    }

    // No predefined route found
    $pos = strrpos($s_address, "/");
    $this->s_controller = substr($s_address, 0, $pos);
    $this->s_method = substr($s_address, ($pos + 1));
    $this->a_arguments = [];
    if( !is_null($this->exception) ){
      $this->a_arguments[] = $this->exception;
    }
    
    if(substr($this->s_controller, 0,1) != '/' ){
      $this->s_controller = '/'.$this->s_controller;
    }
    
    if( $this->checkController() ){
      return $this->class;
    }

    throw new \Http404Exception('Unknown page.');
  }

  private function checkPredefinedRoute($s_address){
    $a_parts = explode('/',$s_address);
    $this->builder->select('routes', '*')->getWhere()->bindString('url', $a_parts[0].'%', 'AND', 'LIKE');
    $database = $this->builder->getResult();

    if( $database->num_rows() == 0 ){
      return false;
    }

    $a_data = $database->fetch_assoc();
    $ids = [];
    foreach($a_data AS $a_page){
      $ids[] = $a_page['id'];
    }

    $this->builder->select('route_parameters', '*')->getWhere()->bindInt('route_id',$ids,'AND','IN');
    $database = $this->builder->getResult();

    $a_arguments = [];
    if( $database->num_rows() > 0 ){
      $a_argumentsRaw = $database->fetch_assoc();
      foreach($a_argumentsRaw AS $a_argument){
        if( !array_key_exists($a_argument['route_id'],$a_arguments) ){
          $a_arguments[ $a_argument['route_id'] ] = [];
        }

        $a_arguments[ $a_argument['route_id'] ][$a_argument['parameter']] = $a_argument['restriction'];
      }
    }

    foreach($a_data AS $a_page ){
      if( array_key_exists($a_page['id'],$a_arguments) ){
        $route = new Route($a_page,$a_arguments[$a_page['id']]);
      }
      else {
        $route = new Route($a_page);
      }
      if( !$route->isValid($s_address) ){
        continue;
      }

      $this->s_controller = $route->getController();
      $this->s_method = $route->getMethod();
      $this->a_arguments = $route->getArguments();
      
      return true;
    }

    return false;
  }

  private function checkController(){
      $s_file = str_replace('\\',DS,$this->s_controller);
      if (!file_exists(WEB_ROOT . $s_file.'.php')) {
	$s_file = strtolower($s_file);
	if (!file_exists(WEB_ROOT . $s_file.'.php')) {
	  return false;
	}
      }

      $s_caller = str_replace('/', '\\', $this->s_controller);

      $_SERVER['SCRIPT_NAME'] = $this->s_controller.'/'.$this->s_method;
      
      $reflector = new \ReflectionClass($s_caller);
      if( !$reflector->hasMethod($this->s_method) ){
	return false;
      }
      if( !$reflector->getMethod($this->s_method)->isPublic() ){
	throw new \RuntimeException('Can not call method '.$this->s_method.' from class '.$s_caller.'. Method is not public.');
      }
      
      $class = \Loader::inject($s_caller);
      if (!method_exists($class, $this->s_method)) {
	$class = null;
	return false;
      }
      
      $this->config->setCall($this->s_controller,$this->s_method);
      
      Routes::checkLogin();

      $this->returnResult = null;
      $this->class = null;
      
      if( count($this->a_arguments) === 0 ){
	$this->returnResult = call_user_func([$class,$this->s_method]);
      }
      else {
	$this->returnResult = call_user_func_array([$class, $this->s_method], $this->a_arguments);
      }      
      
      $this->class = $class;
      
      return true;
  }
  
  private function checkLogin(){    
    \Profiler::profileSystem('core/models/Privileges', 'Checking access level');
    \Loader::inject('\youconix\core\models\Privileges')->checkLogin();
    \Profiler::profileSystem('core/models/Privileges', 'Checking access level completed');
  }
  
  public function getResult(){
    return $this->returnResult;
  }
}

class Route {
  private $s_method;
  private $s_path;
  private $a_conditions = [];
  private $s_controller;
  private $s_function;
  private $a_arguments = [];

  public function __construct($a_data,$a_arguments = []) {
    $this->s_method = $a_data['type'];
    $this->s_path = $a_data['url'];
    $this->s_controller = $a_data['controller'];
    $this->s_function = $a_data['function'];

    $this->a_conditions = $a_arguments;
  }

  private function render() {
    $s_regex = $this->s_path;
    foreach ($this->a_conditions AS $s_key => $s_condition) {
      $s_regex = str_replace('{' . $s_key . '}', '(' . $s_condition . ')', $s_regex);
    }

    $a_arguments = null;
    if( preg_match_all('/{([a-zA-Z0-9_\-]+)}/', $s_regex,$a_arguments) ){
      $i_num = count($a_arguments);
      for($i=1; $i<$i_num; $i++){
        $s_regex = str_replace('{'.$a_arguments[$i][0].'}','(.+)',$s_regex);
        $this->a_conditions[$a_arguments[$i][0]] = '.+';
      }
    }

    return '/^' . str_replace('/', '\/', $s_regex) . '$/si';
  }

  public function isValid($s_address) {
    $s_regex = $this->render();
    
    $a_matches = [];
    if( !preg_match_all($s_regex, $s_address, $a_matches) ){
      return false;
    }

    $this->a_arguments = [];
    $a_keys = array_keys($this->a_conditions);
    for ($i = 1; $i < count($a_matches); $i++) {
      $this->a_arguments[$a_keys[($i-1)]] = $a_matches[$i][0];
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
