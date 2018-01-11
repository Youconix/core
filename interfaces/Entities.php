<?php

interface Entities{
  public function buildMap();
  
  public function buildProxies();
  
  /**
   * Returns the entity layout
   *
   * @param string $s_entity
   * @return array
   */
  public function get($s_entity);
  
  /**
   * 
   * @param string $s_repository
   * @return \youconix\core\ORM\Repository
   * @throws \RuntimeException
   */
  public function getRepository($s_repository);
  
  /**
   * string
   */
  public function getTimezone();
}