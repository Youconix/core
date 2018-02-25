<?php

namespace youconix\Core\Services;

/**
 * Cache service
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @version 1.0
 * @since 2.0
 */
class Cache extends \youconix\Core\Services\AbstractService implements \CacheInterface
{

  /**
   *
   * @var \Builder
   */
  protected $builder;

  /**
   *
   * @var \youconix\core\services\FileHandler
   */
  protected $file;

  /**
   *
   * @var \ConfigInterface
   */
  protected $config;

  /**
   *
   * @var \SettingsInterface
   */
  protected $settings;

  /**
   *
   * @var \youconix\core\Routes
   */
  protected $routes;

  /**
   *
   * @var \Headers
   */
  protected $headers;
  protected $s_language;
  protected $bo_cache;

  /**
   *
   * @param \youconix\core\services\FileHandler $file
   * @param \ConfigInterface $config
   * @param \Headers $headers
   * @param \Builder $builder
   * @param \youconix\core\Routes $routes
   */
  public function __construct(\youconix\core\services\FileHandler $file,
                              \ConfigInterface $config, \Headers $headers, \Builder $builder,
                              \youconix\core\Routes $routes)
  {
    $this->config = $config;
    $this->file = $file;
    $this->settings = $config->getSettings();
    $this->headers = $headers;
    $this->builder = $builder;
    $this->routes = $routes;

    $s_directory = $this->getDirectory();
    if (!$this->file->exists($s_directory . 'site')) {
      $this->file->newDirectory($s_directory . 'site');
    }
  }

  /**
   * Returns if the object schould be treated as singleton
   *
   * @return boolean True if the object is a singleton
   */
  public static function isSingleton()
  {
    return true;
  }

  /**
   * Returns the cache directory
   *
   * @return string The directory
   */
  protected function getDirectory()
  {
    
    return WEB_ROOT . DS . 'files' . DS . 'cache' . DS;
  }

  /**
   * Returns the cache file part seperator
   *
   * @return string The seperator
   */
  protected function getSeperator()
  {
    return '===============|||||||||||||||||||||||=================';
  }

  /**
   * Returns if the file should be cached
   *
   * @return boolean
   */
  protected function shouldCache()
  {
    if (!is_null($this->bo_cache)) {
      return $this->bo_cache;
    }

    if (!$this->settings->exists('cache/status') || $this->settings->get('cache/status') != 1) {
      $this->bo_cache = false;
      return $this->bo_cache;
    }
    $s_page = $_SERVER['REQUEST_URI'];
    $a_pages = explode('?', $s_page);

    $this->builder->select('no_cache', 'id')
	->getWhere()
	->bindString(' page', $s_page)
	->bindString('page', $a_pages[0]);

    $service_database = $this->builder->getResult();
    if ($service_database->num_rows() > 0) {
      $this->bo_cache = false;
    } else {
      $this->bo_cache = true;
    }

    return $this->bo_cache;
  }

  /**
   * Checks the cache and displays it
   *
   * @return boolean False if no cache is present
   */
  public function checkCache()
  {
    if (($_SERVER['REQUEST_METHOD'] != 'GET') || !$this->shouldCache()) {
      return false;
    }

    $s_language = $this->config->getLanguage();
    $s_directory = $this->getDirectory();

    if (!$this->file->exists($s_directory . 'site' . DS . $s_language)) {
      $this->file->newDirectory($s_directory . 'site' . DS . $s_language);
      return false;
    }

    $s_target = $this->getCurrentPage();

    if (!$this->file->exists($s_target)) {
      return false;
    }

    $a_file = explode($this->getSeperator(), $this->file->readFile($s_target));
    $i_timeout = $this->settings->get('cache/timeout');

    if ((time() - $a_file[0]) > $i_timeout) {
      $this->file->deleteFile($s_target);
      return false;
    }

    $this->displayCache($a_file);
  }

  /**
   * Displays the cached page
   *
   * @param array $a_file
   *            The page parts
   */
  protected function displayCache($a_file)
  {
    $a_headers = unserialize($a_file[1]);
    $this->headers->importHeaders($a_headers);
    $this->headers->printHeaders();
    echo ($a_file[2]);
    die();
  }

  /**
   * Writes the renderd page to the cache
   *
   * @param string $s_output
   *            The rendered page
   */
  public function writeCache($s_output)
  {
    if (!$this->shouldCache()) {
      return;
    }

    $s_headers = serialize($this->headers->getHeaders());

    $s_output = time() . $this->getSeperator() . $s_headers . $this->getSeperator() . $s_output;

    $s_language = $this->config->getLanguage();
    $s_target = $this->getCurrentPage();
    $this->file->writeFile($s_target, $s_output);
  }

  /**
   * Returns the current page address
   *
   * @return string The address
   */
  protected function getCurrentPage()
  {
    return $this->getAddress($_SERVER['REQUEST_URI']);
  }

  /**
   * Returns the full address for the given page
   *
   * @param string $s_page
   *            The page ($_SERVER['REQUEST_URI'])
   * @return string The address
   */
  protected function getAddress($s_page)
  {
    return $this->getDirectory() . 'site' . DS . $s_language . DS . str_replace('/',
										'_', $s_page) . '.html';
  }

  /**
   * Clears the given page from the site cache
   *
   * @param string $s_page
   */
  public function clearPage($s_page)
  {
    $s_page = $this->getAddress($s_page);

    if ($this->file->exists($s_page)) {
      $this->file->deleteFile($s_page);
    }
  }

  /**
   * Clears the language cache (.mo)
   */
  public function cleanLanguageCache()
  {
    if ($this->settings->exists('language/type') && $this->settings->get('language/type') == 'mo') {
      clearstatcache();
    }
  }

  /**
   * Clears the site cache
   */
  public function clearSiteCache()
  {
    $site = $this->getDirectory() . 'site';
    $this->file->deleteDirectoryContent($site);
    
    $views = $this->getDirectory() . 'views';
    $this->file->deleteDirectoryContent($views);
  }

  /**
   * Returns the no cache pages
   *
   * @return array The pages
   */
  public function getNoCachePages()
  {
    $a_pages = [];

    $this->builder->select('no_cache', '*');
    $service_database = $this->builder->getResult();
    if ($service_database->num_rows() > 0) {
      $a_pages = $service_database->fetch_assoc();
    }

    return $a_pages;
  }

  /**
   * Adds a no-cache page
   *
   * @param string $page
   * @return int
   */
  public function addNoCachePage($page)
  {
    \youconix\core\Memory::type('string', $page);

    $addresses = $this->routes->getAllAddresses();
    if (!in_array($page, $addresses)) {
      return -1;
    }

    $this->builder->select('no_cache', 'id')
	->getWhere()
	->bindString('page', $page);
    $service_database = $this->builder->getResult();
    if ($service_database->num_rows() != 0) {
      return -1;
    }

    $database = $this->builder->insert('no_cache')
	->bindString('page', $page)
	->getResult();

    return $database->getId();
  }

  /**
   * Deletes the given no-cache page
   *
   * @param int $id
   */
  public function deleteNoCache($id)
  {
    \youconix\core\Memory::type('int', $id);

    $this->builder->delete('no_cache')
	->getWhere()
	->bindInt('id', $id);
    $this->builder->getResult();
  }
}
