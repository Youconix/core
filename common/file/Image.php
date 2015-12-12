<?php 
namespace youconix\core\common\file;
/**
 * Image file class
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author    Rachelle Scheijen
 * @since     2.0
 * @see \SplFileObject
 */

class Image extends \youconix\core\common\file\File {
    protected $i_width = null;
    protected $i_height;
    protected $s_attr;
    
    /**
     * @return int
     */
    public function getHeight(){
        $this->getSizes();
        
        return $this->i_width;
    }
    
    /**
     * @return int
     */
    public function getWidth(){
        $this->getSizes();
        
        return $this->i_height;
    }
    
    /**
     * @return string
     */
    public function getAttribute(){
        $this->getSizes();
        
        return $this->s_attr;
    }
    
    /**
     * Collects the data of the image
     */
    protected function getSizes(){
        if( is_null($this->i_width) ){
            list($width,$height,$type,$attr) = getimagesize($this->getRealPath(),FILEINFO_MIME_TYPE);
            
            $this->i_width = $width;
            $this->i_height = $height;
            $this->s_attr = $attr;
        }
    }
}
?>