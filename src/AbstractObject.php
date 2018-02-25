<?php

namespace youconix\Core;

abstract class AbstractObject
{

  /**
   * Returns if the object should be treated as singleton
   *
   * @return boolean True if the object is a singleton
   */
  public static function isSingleton()
  {
    return false;
  }
}