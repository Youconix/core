<?php

namespace youconix\core\models;

/**
 * Checks the access privileges from the current page
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 2.0
 */
class Privileges {

  /**
   *
   * @var \Headers
   */
  protected $headers;

  /**
   *
   * @var \Config
   */
  protected $config;

  /**
   *
   * @var \Builder
   */
  protected $builder;

  /**
   *
   * @var \youconix\core\models\Groups
   */
  protected $groups;
  
  /**
   *
   * @var \youconix\core\auth\Auth
   */
  protected $auth;
  
  /**
   *
   * @var \Session $session
   */
  protected $session;
  
  protected $i_group;
  
  protected $i_level;

  /**
   * PHP 5 constructor
   *
   * @param \Headers $headers            
   * @param \Builder $builder            
   * @param \youconix\core\models\Groups $groups          
   * @param \Config $config      
   * @param \youconix\core\auth\Auth $auth
   * @param \Session $session
   */
  public function __construct(\Headers $headers, \Builder $builder, \youconix\core\models\Groups $groups, \youconix\core\auth\Auth $auth, 
	  \Config $config,\Session $session) {
    $this->headers = $headers;
    $this->builder = $builder;
    $this->groups = $groups;
    $this->config = $config;
    $this->auth = $auth;
    $this->session = $session;
  }

  /**
   * Returns if the object schould be treated as singleton
   *
   * @return boolean True if the object is a singleton
   */
  public static function isSingleton() {
    return true;
  }

  /**
   * Checks or the user is logged in and haves enough rights.
   * Define the groep and level to overwrite the default rights for the page
   *
   * @param int $i_group
   *            id, optional
   * @param int $i_level
   *            level, optional
   * @param int $i_commandLevel
   *            The minimun level for the command, optional
   */
  public function checkLogin($i_group = -1, $i_level = -1, $i_commandLevel = -1) {
    \youconix\core\Memory::type('int', $i_group);
    \youconix\core\Memory::type('int', $i_level);
    \youconix\core\Memory::type('int', $i_commandLevel);

    if (stripos($this->config->getPage(), '/phpunit') !== false) {
      /* Unit test */
      return;
    }
    
    $this->checkPageLevels($i_group,$i_level);

    $this->checkSSL($i_level);
    
    $user = $this->auth->getUser();

    if ($this->i_level == \Session::ANONYMOUS) {
      return;
    }

    /* Get redict url */
    $s_base = $this->config->getBase();
    $s_page = $_SERVER['REQUEST_URI'];
    if ($s_base != '/') {
      $s_page = str_replace($s_base, '', $s_page);
    }
    while(substr($s_page,0,1) == '/'){
        $s_page = substr($s_page,1);
    }

    if( is_null($user) ){
      if ($this->config->isAjax()) {
	$this->headers->http401();
	$this->headers->printHeaders();
	die();
      }

      $this->session->set('page', $s_page);
      throw new \Http401Exception('Authorisation required.');
    }

    /* Check fingerprint */
    $i_userLevel = $this->groups->getLevelByGroupID($this->i_group, $user->getID());

    if (($i_userLevel < $i_level)) {
      /*
       * Insuffient rights or no access too the group No access
       */
      throw new \Http403Exception('Access denied.');
    }

    $this->checkCommand($i_commandLevel, $user->getID());
  }
  
  protected function checkPageLevels($i_group,$i_level){
    if ($i_group == - 1 || $i_level == - 1) {
      $this->builder->select('group_pages', 'groupID,minLevel')
	      ->getWhere()->bindString('page', $this->config->getPage());
      $service_Database = $this->builder->getResult();

      $i_group = 1;
      $i_level = \Session::ANONYMOUS;

      if ($service_Database->num_rows() > 0) {
	$i_level = (int) $service_Database->result(0, 'minLevel');
	$i_group = (int) $service_Database->result(0, 'groupID');
      }
    }
    
    $this->i_level = $i_level;
    $this->i_group = $i_group;
  }

  /**
   * Checks the command privaliges
   *
   * @param int $i_commandLevel
   *            The minimun command access level, -1 for auto detect
   * @param int $i_userid
   *            The userid
   */
  protected function checkCommand($i_commandLevel, $i_userid) {
    if ($i_commandLevel !== -1) {
      return;
    }
      $this->builder->select('group_pages_command', 'groupID,minLevel')
	      ->getWhere()->bindString('page', $this->config->getPage())->bindString('command', $this->config->getCommand());

      $service_Database = $this->builder->getResult();
      if ($service_Database->num_rows() > 0) {
	$i_commandLevel = (int) $service_Database->result(0, 'minLevel');
	$i_group = (int) $service_Database->result(0, 'groupID');

	$this->i_level = $this->groups->getLevelByGroupID($i_group, $i_userid);
      }

    if ( $this->i_level < $i_commandLevel) {
      /*
       * Insuffient rights or no access too the group No access
       */
      throw new \Http403Exception('Access denied.');
    }
  }

  /**
   * Checks the ssl setting
   *
   * @param int $i_level
   *            The minimun page level
   */
  protected function checkSSL($i_level) {
    $i_ssl = $this->config->isSslEnabled();
    if (defined('FORCE_SSL')) {
      $i_ssl = \Settings::SSL_ALL;
    }

    if ($this->config->isSLL() || ($i_ssl == \Settings::SSL_DISABLED)) {
      return;
    }

    if (($i_level == \Session::ANONYMOUS) && ($i_ssl == \Settings::SSL_LOGIN) && (stripos($_SERVER['REQUEST_URI'], 'authorization')) === false) {
      return;
    }

    $this->headers->redirect('https://' . $_SERVER['HTTP_HOST'] . '/' . $_SERVER['REQUEST_URI']);
    $this->headers->printHeaders();
    exit();
  }

}
