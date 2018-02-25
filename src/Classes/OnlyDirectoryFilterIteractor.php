<?php

namespace youconix\Core\Classes;

/**
 * Filters the file list to accept only directories
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author    Rachelle Scheijen
 * @since     2.0
 */

class OnlyDirectoryFilterIteractor extends \FilterIterator
{
  /**
   * @return bool
   */
  public function accept()
  {
    return $this->current()->isDir();
  }
}