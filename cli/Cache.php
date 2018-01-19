<?php

namespace youconix\core\cli;

class Cache extends \youconix\core\templating\CliController {
  /**
   * @var\Cache
   */
  private $cache;
  
  /** 
   * @var\youconix\core\Routes
   */
  private $routes;
  
  /**
   * @var \youconix\core\ORM\EntityHelper
   */
  private $entityHelper;
  
  /**
   *
   * @var \youconix\core\services\CurlManager
   */
  private $curl;
  
  /**
   * 
   * @param \Cache $cache
   * @param \youconix\core\Routes $routes
   * @param \youconix\core\ORM\EntityHelper $entityHelper
   * @param \youconix\core\services\CurlManager $curl
   */
  public function __construct(\Cache $cache, \youconix\core\Routes $routes,
			      \youconix\core\ORM\EntityHelper $entityHelper,
			      \youconix\core\services\CurlManager $curl)
  {
    parent::__construct();
    
    $this->cache = $cache;
    $this->routes = $routes;
    $this->entityHelper = $entityHelper;
    $this->curl = $curl;
  }
  
  public function clear()
  {
    $this->message('Clearing cache in progress...');
    
    try {
      $this->cache->clearSiteCache();
      $this->cache->cleanLanguageCache();
      $this->entityHelper->dropCache();
      $this->routes->dropCache();

      $this->message('Clearing cache complete');
    }
    catch(\Exception $e){
      $this->message('Clearing cache failed.'.PHP_EOL.'Reason: '.$e->getMessage());
    }
  }
  
  public function warmup(){
    $this->message('Warming up cache...');
    
    $routes = $this->routes->getAllAddresses();
    
    foreach($routes as $route){
      if (strpos($route, '{') !== false){
	$this->message('Skipping dynamic route '.$route.'.');
      }
      
      $this->curl->performGetCall($route, []);
    }
    
    $this->message('Cache warmup complete');
  }
}