<?php 
namespace youconix\core\common\file;

/**
 * File class
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author    Rachelle Scheijen
 * @since     2.0
 * @see \SplFileObject
 */

class File extends \SplFileObject {
    /**
     * Creates a new file object
     * 
     * @param string $s_filename    The filename
     * @return \youconix\core\common\file\File
     */
    public static function create($s_filename){
        return new \youconix\core\common\File($s_filename);
    }
    
    /**
     * Returns the mime type
     * 
     * @return string
     */
    public function getMimetype(){
        $info = new \finfo();
        $fileinfo = $info->file($this->getRealPath(),FILEINFO_MIME_TYPE);
        
        return $fileinfo;
    }
}
?>