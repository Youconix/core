<?php

interface EntityManager {  
  public function getRepository($s_repository);
  
  /**
   * 
   * @param \Entity $entity
   * @throws \ValidationException
   */
  public function persist(\Entity $entity);
  
  /**
   * 
   * @param \Entity $entity
   */
  public function delete(\Entity $entity);
  
  public function flush();
  
  /**
   * @return \Builder
   */
  public function getBuilder();
  
  /**
   * @return \Entities
   */
  public function getHelper();
}

