<?php
namespace core\common\file;

class DefaultMimetypes {
    /**
     * 
     * @var \core\common\file\FileExtension
     */
    protected $fileExtension;
    protected $a_mimetypes = array();
    
    public function __construct(\core\common\file\FileExtension $fileExtension){
        $this->fileExtension = $fileExtension;
        
        $this->setMimetypes();
    }
    
    protected function setMimetypes(){
        /* Images */
        $this->createMimetype('gif','image/gif','image','GIF image');
        $this->createMimetype('jpg','image/jpg','image','JPEG image');
        $this->createMimetype('jpeg','image/jpeg','image','JPEG image');
        $this->createMimetype('png','image/png','image','PNG image');
        $this->createMimetype('bmp','image/bmp','image','BMP image');
        $this->createMimetype('tiff','image/tiff','image','TIFF image');
        $this->createMimetype('svg',array('image/svg','image/svg+xml'),'image','SVG image');
        $this->createMimetype('webp','image/webp','image','WEBP image');
        
        /* Video */
        
        /* Source files */
    }
    
    protected function createMimetype($s_extension,$a_mimetypes,$s_category,$s_description = ''){
        $extension = clone $this->fileExtension;
        
        $extension->init($s_extension,$a_mimetypes,$s_category,$s_description);
        $this->a_mimetypes[$s_extension] = $extension;
    }
    
    public function __get($s_name){
        if( array_key_exists($s_name,$this->a_mimetypes) ){
            return $this->a_mimetypes[$s_name];
        }
        
        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $s_name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);
        return null;
    }
}