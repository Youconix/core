<?php

namespace youconix\core\ORM;

final class EntityManager implements \EntityManager{  
  /**
   *
   * @var \Builder
   */
  private $builder;
  
  private $a_repositories = [];
  
  private $a_persist = [];
  private $a_delete = [];
  
  /**
   *
   * @var \Entities $helper
   */
  private $helper;  

  public function __construct(\Builder $builder, \Entities $helper) {
    $this->builder = $builder;
    $this->helper = $helper;
    
    $this->helper->buildMap();
    $this->helper->buildProxies();
  }

  /**
   * Returns if the object should be treated as singleton
   *
   * @return boolean True if the object is a singleton
   */
  public static function isSingleton() {
    return true;
  }
  
  /**
   * 
   * @param string $s_repository
   * @return \youconix\core\ORM\Repository
   * @throws \RuntimeException
   */
  public function getRepository($s_repository)
  {
    if (in_array($s_repository, $this->a_repositories)) {
      return $this->a_repositories[$s_repository];
    }
    
    $a_repository = $this->helper->getRepository($s_repository);
    $repository = \Loader::inject($a_repository['repository']);
    
    $this->a_repositories[$s_repository] = $repository;
    return $this->a_repositories[$s_repository];
  }
  
  /**
   * 
   * @param \Entity $entity
   * @throws \ValidationException
   */
  public function persist(\Entity $entity)
  {
    $this->a_persist[] = $entity;
  }
  
  /**
   * 
   * @param \Entity $entity
   */
  public function delete(\Entity $entity)
  {
    $this->a_delete = $entity;
  }
  
  public function flush()
  {
    
  }
  
  /**
   * @return \Builder
   */
  public function getBuilder()
  {
    return $this->builder;
  }
  
  /**
   * @return \Entities
   */
  public function getHelper()
  {
    return $this->helper;
  }
}