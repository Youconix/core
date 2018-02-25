<?php

namespace youconix\Core\Cli;

class Cache extends \youconix\Core\Templating\CliController
{
  /** @var\CacheInterface */
  private $cache;

  /**  @var\youconix\Core\Routes */
  private $routes;

  /** @var \youconix\core\ORM\EntityHelper */
  private $entityHelper;

  /** @var \youconix\core\services\CurlManager */
  private $curl;

  /**
   * @param \CacheInterface $cache
   * @param \youconix\Core\Routes $routes
   * @param \youconix\Core\ORM\EntityHelper $entityHelper
   * @param \youconix\Core\Services\CurlManager $curl
   */
  public function __construct(\CacheInterface $cache, \youconix\Core\Routes $routes,
                              \youconix\Core\ORM\EntityHelper $entityHelper,
                              \youconix\Core\Services\CurlManager $curl)
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
    } catch (\Exception $e) {
      $this->message('Clearing cache failed.' . PHP_EOL . 'Reason: ' . $e->getMessage());
    }
  }

  public function warmup()
  {
    $this->message('Warming up cache...');

    $routes = $this->routes->getAllAddresses();

    foreach ($routes as $route) {
      if (strpos($route, '{') !== false) {
        $this->message('Skipping dynamic route ' . $route . '.');
      }

      $this->curl->performGetCall($route, []);
    }

    $this->message('Cache warmup complete');
  }
}