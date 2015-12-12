<?php 
namespace core\common\file;

class FileExtension {
    private $s_extension;
    private $a_mimetypes;
    private $s_description;
    private $s_category;
    
    public function init($s_extension,$a_mimetypes,$s_category,$s_description = ''){
        if( !is_array($a_mimetypes) ){
            $a_mimetypes = array($a_mimetypes);
        }
        
        $this->s_extension = $s_extension;
        $this->a_mimetypes = $a_mimetypes;
        $this->s_category = $s_category;
        $this->s_description = $s_description;
    }
    
    public function getExtension(){
        return $this->s_extension;
    }
    
    public function getMimetypes(){
        return $this->a_mimetypes;
    }
    
    public function getCategory(){
        return $this->s_category;
    }
    
    public function getDescription(){
        return $this->s_description;
    }
}