<?php
namespace youconix\core\classes;

/**
 * Filters the read directory
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author    Rachelle Scheijen
 * @since     2.0
 */

class DirectoryFilterIteractor extends \FilterIterator {
    private $a_directoryFilter;
     
    public function __construct(\Iterator $iterator , $a_filters )
    {
        parent::__construct($iterator);
        
        $this->a_directoryFilter = $a_filters;
    }
     
    public function accept()
    {
        $item = $this->current()->getFilename();

        foreach( $this->a_directoryFilter AS $s_filter ){
            $s_filter = str_replace(array('.','/','*'),array('\.','\/','.+'),$s_filter);
            
            if( substr($s_filter,0,1) == '!' ){
                $s_filter = substr($s_filter, 1);
                if( preg_match('/'.$s_filter.'/',$item) ){
                    return false;
                }
            }
            else {
                if( !preg_match('/'.$s_filter.'/',$item) ){
                    return false;
                }
            }
        }
        
        return true;
    }
}