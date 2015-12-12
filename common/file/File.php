<?php 
namespace \core\common\file;

class File extends \SplFileObject {
    /**
     * Creates a new file object
     * 
     * @param string $s_filename    The filename
     * @return \core\common\file\File
     */
    public static function create($s_filename){
        return new \core\common\File($s_filename);
    }
    
    public function getMimetype(){
        $info = new \finfo();
        $fileinfo = $info->file($this->getRealPath(),FILEINFO_MIME_TYPE);
        
        return $fileinfo;
    }
}
?>