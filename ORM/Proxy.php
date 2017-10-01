<?php

namespace youconix\core\ORM;

use youconix\core\ORM\Entity;

class Proxy implements \Iterator, \ArrayAccess {
  private $repository;
  private $s_field;
  private $value;
  private $bo_loaded = false;
  private $a_values = [];
  
  public function create(Repository $repository, $s_field, $value){
    $this->repository = $repository;
    $this->s_field = $s_field;
    $this->value = $value;
	
  }
  
  private function checkLoad()
  {
    if (!$this->bo_loaded) {
      $this->a_values = $this->repository->findBy([$this->s_field => $this->value]);
      $this->bo_loaded = true;
    }
  }

  /**
   * 
   * @return Entity
   */
  public function current()
  {
    $this->checkLoad();
    return current($this->a_values);
  }

  /**
   * 
   * @return string
   */
  public function key()
  {
    $this->checkLoad();
    return key($this->a_values);
  }

  /**
   * 
   * @return Entity
   */
  public function next()
  {
    $this->checkLoad();
    return next($this->a_values);
  }

  public function rewind()
  {
    $this->checkLoad();
    reset($this->a_values);
  }

  /**
   * 
   * @return bool
   */
  public function valid()
  {
    $this->checkLoad();
    $i_key = $this->key();
    return ($i_key !== null && $i_key !== false);
  }

  public function offsetExists($offset)
  {
    $this->checkLoad();
    return (array_key_exists($offset, $this->a_values));
  }

  public function offsetGet($offset)
  {
    $this->checkLoad();
    return $this->a_values[$offset];
  }

  public function offsetSet($offset, $value)
  {
    $this->checkLoad();
    $this->a_values[$offset] = $value;
  }

  public function offsetUnset($offset)
  {
    $this->checkLoad();
    unset($this->a_values[$offset]);
  }
}