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
   * string
   */
  public function getTimezone();
}