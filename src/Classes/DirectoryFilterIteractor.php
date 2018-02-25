<?php

namespace youconix\Core\Classes;

/**
 * Filters the read directory
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author    Rachelle Scheijen
 * @since     2.0
 */

class DirectoryFilterIteractor extends \FilterIterator
{
  private $directoryFilter;


  /**
   * DirectoryFilterIteractor constructor.
   * @param \Iterator $iterator
   * @param array $filters
   */
  public function __construct(\Iterator $iterator, array $filters)
  {
    parent::__construct($iterator);

    $this->directoryFilter = $filters;
  }

  /**
   * @return bool
   */
  public function accept()
  {
    $item = $this->current()->getFilename();

    foreach ($this->directoryFilter AS $filter) {
      $filter = str_replace(array('.', '/', '*'), array('\.', '\/', '.+'), $filter);

      if (substr($filter, 0, 1) == '!') {
        $filter = substr($filter, 1);
        if (preg_match('/' . $filter . '/', $item)) {
          return false;
        }
      } else {
        if (!preg_match('/' . $filter . '/', $item)) {
          return false;
        }
      }
    }

    return true;
  }
}