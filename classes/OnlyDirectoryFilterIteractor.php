<?php
namespace core\classes;

class OnlyDirectoryFilterIteractor extends \FilterIterator {
    public function accept()
    {
        return $this->current()->isDir();
    }
}