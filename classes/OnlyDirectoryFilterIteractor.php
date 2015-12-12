<?php
namespace youconix\core\classes;

/**
 * Filters the file list to accept only directories
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author    Rachelle Scheijen
 * @since     2.0
 */

class OnlyDirectoryFilterIteractor extends \FilterIterator {
    public function accept()
    {
        return $this->current()->isDir();
    }
}