<?php

namespace youconix\Core\Models;

/**
 * Checks the access privileges from the current page
 * @since 2.0
 */
class Privileges
{

  /**
   *
   * @var \HeadersInterface
   */
  protected $headers;

  /**
   *
   * @var \ConfigInterface
   */
  protected $config;

  /**
   *
   * @var \BuilderInterface
   */
  protected $builder;

  /**
   *
   * @var \youconix\Core\Repositories\UserGroup
   */
  protected $groups;

  /**
   *
   * @var \youconix\Core\Auth\Auth
   */
  protected $auth;

  /**
   *
   * @var \SessionInterface $session
   */
  protected $session;

  /**
   *
   * @var \youconix\Core\Services\FileHandler
   */
  protected $file;

  /**
   *
   * @var \stdClass
   */
  protected $map;
  protected $s_cacheFile;

  /**
   * PHP 5 constructor
   *
   * @param \HeadersInterface $headers
   * @param \BuilderInterface $builder
   * @param \youconix\Core\Repositories\UserGroup $groups
   * @param \youconix\Core\Auth\Auth $auth
   * @param \ConfigInterface $config
   * @param \SessionInterface $session
   * @param \youconix\Core\Services\FileHandler $file
   */
  public function __construct(\HeadersInterface $headers, \BuilderInterface $builder,
                              \youconix\Core\Repositories\UserGroup $groups,
                              \youconix\Core\Auth\Auth $auth, \ConfigInterface $config, \SessionInterface $session,
                              \youconix\Core\Services\FileHandler $file
			      )
  {
    $this->headers = $headers;
    $this->builder = $builder;
    $this->groups = $groups;
    $this->config = $config;
    $this->auth = $auth;
    $this->session = $session;
    $this->file = $file;

    $s_cacheDir = $this->config->getCacheDirectory();
    $this->s_cacheFile = $s_cacheDir . 'privilegesMap.php';
    $this->buildMap();
  }

  protected function buildMap()
  {
    $this->map = new \stdClass();
    $this->map->pages = [];

    if ($this->file->exists($this->s_cacheFile) && !defined('DEBUG')) {
      $this->readMap();
      return;
    }

    $a_pages = $this->builder->select('group_pages', '*')->getResult()->fetch_object();
    $a_pageFunctions = $this->builder->select('group_pages_command', '*')->getResult()->fetch_object();

    foreach ($a_pages as $page) {
      $name = $page->page;
      $page->commands = [];
      $this->map->pages[$name] = $page;
    }

    foreach ($a_pageFunctions as $function) {
      $name = $function->page;
      $this->map->pages[$name]->commands[$function->group_pages_command] = $function;
    }

    if (!defined('DEBUG')) {
      $this->file->writeFile($this->s_cacheFile, serialize($this->map));
    }
  }

  protected function readMap()
  {
    $s_content = $this->file->readFile($this->s_cacheFile);
    $this->map = unserialize($s_content);
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

  /**
   * Checks or the user is logged in and haves enough rights.
   */
  public function checkLogin()
  {
    if (stripos($this->config->getPage(), '/phpunit') !== false) {
      /* Unit test */
      return;
    }

    $i_level = \SessionInterface::ANONYMOUS;
    $i_group = 1;

    $page = $this->getPage();
    if (!is_null($page)) {
      $i_level = $page['level'];
      $i_group = $page['groupId'];
    }
    $this->checkSSL($i_level);

    if ($i_level == \SessionInterface::ANONYMOUS) {
      return;
    }

    $user = $this->auth->getUser();
    if (!defined('USERID')) {
      define('USERID', $user->getUserId());
    }

    /* Get redict url */
    $s_base = $this->config->getBase();
    $s_page = $_SERVER['REQUEST_URI'];
    if ($s_base != '/') {
      $s_page = str_replace($s_base, '', $s_page);
    }

    if (is_null($user->getUserId())) {
      if ($this->config->isAjax()) {
	$this->headers->http401();
	$this->headers->printHeaders();
	die();
      }

      $this->session->set('page', $s_page);
      throw new \Http401Exception('Authorisation required.');
    }

    /* Check access level */
    $i_userLevel = (!is_null($user->getGroup($i_group)) ? $user->getGroup($i_group)->getLevel() : \SessionInterface::ANONYMOUS);
    
    if (($i_userLevel < $i_level)) {
      /*
       * Insuffient rights or no access to the group. No access
       */
      throw new \Http403Exception('Access denied.');
    }
  }

  protected function getPage()
  {
    $s_page = $this->config->getPage();
    if (substr($s_page, 0, 1) == '/') {
      $s_page = substr($s_page, 1);
    }
    
    if (!array_key_exists($s_page, $this->map->pages)) {
      return;
    }

    $page = $this->map->pages[$s_page];

    $i_level = $page->minLevel;
    $i_groupId = $page->groupID;

    $s_command = $this->config->getCommand();
    if (array_key_exists($s_command, $page->commands)) {
      $i_level = $page->commands[$s_command]->minLevel;
      $i_groupId = $page->commands[$s_command]->groupID;
    }

    return [
	'level' => $i_level,
	'groupId' => $i_groupId
    ];
  }

  /**
   * Checks the ssl setting
   *
   * @param int $i_level
   *            The minimum page level
   */
  protected function checkSSL($i_level)
  {
    $i_ssl = $this->config->isSslEnabled();
    if (defined('FORCE_SSL')) {
      $i_ssl = \SettingsInterface::SSL_ALL;
    }

    if ($this->config->isSLL() || ($i_ssl == \SettingsInterface::SSL_DISABLED)) {
      return;
    }

    if (($i_level == \SessionInterface::ANONYMOUS) && ($i_ssl == \SettingsInterface::SSL_LOGIN) &&
	(stripos($_SERVER['REQUEST_URI'], 'AuthorizationInterface')) === false) {
      return;
    }

    $this->headers->redirect('https://' . $_SERVER['HTTP_HOST'] . '/' . $_SERVER['REQUEST_URI']);
    $this->headers->printHeaders();
    exit();
  }
}
