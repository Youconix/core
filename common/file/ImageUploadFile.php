<?php 
namespace core\common\file;

class ImageUploadFile extends \core\common\file\UploadFile {
    protected $i_maxSize = -1;
    protected $i_maxHeight = -1;
    protected $i_maxWidth = -1;
    
    public function __construct(\core\services\Language $language, \core\common\file\DefaultMimetypes $mimetypes){
        parent::__construct($language);
        
        $this->setMimeAllowed($mimetypes->jpg);
        $this->setMimeAllowed($mimetypes->jpeg);
        $this->setMimeAllowed($mimetypes->gif);
        $this->setMimeAllowed($mimetypes->png);
    }
    
    public function setMaxSize($i_maxSize){
        \core\Memory::type('int', $i_maxSize);
        
        $this->i_maxSize = $i_maxSize;
    }
    
    public function setMaxHeigh($i_maxHeight){
        \core\Memory::type('int', $i_maxHeight);
        
        $this->i_maxHeight = $i_maxHeight;
    }
    
    public function setMaxWidth($i_maxWidth){
        \core\Memory::type('int', $i_maxWidth);
        
        $this->i_maxWidth = $i_maxWidth;
    }
    
    /**
     * Performs the upload and configurates the uploader
     *
     * @param string $s_name
     *            The field name
     * @param string $s_target
     *            The target directory
     * @param string $s_newName
     *            The new name, optional
     * @param int   $i_maxSize  The maximun image size, optional
     * @param int   $i_maxHeigth The maximun image height, optional
     * @param int   $i_maxWidth  The maximun image width, optional 
     * @return number The status code
     *         0 Upload succeeded
     *         -1 Upload failed
     *         -2 File bigger then server limit
     *         -3 File has an invalid extension/mimetype
     *         -4 Moving of the file failed. Check target permissions
     *         -5 Image bigger then set limit
     *         -6 Image bigger then set height or width
     */
    public function upload($s_name, $s_target, $s_newName = '',$i_maxSize = -1,$i_maxHeigth = -1,$i_maxWidth = -1)
        
    {
        $this->setMaxSize($i_maxSize);
        $this->setMaxHeigh($i_maxHeight);
        $this->setMaxWidth($i_maxWidth);
        
        $this->doUpload($s_target.$s_newName);
    }
    
    /**
     * Performs the upload
     *
     * @param string $s_name
     *            The field name
     * @param string $s_target
     *            The target directory
     * @param string $s_newName
     *            The new name, optional
     * @return number The status code
     *         0 Upload succeeded
     *         -1 Upload failed
     *         -2 File bigger then server limit
     *         -3 File has an invalid extension/mimetype
     *         -4 Moving of the file failed. Check target permissions
     *         -5 Image bigger then set limit
     *         -6 Image bigger then set height or width
     */
    public function doUpload($s_target,$s_newName = '')
    {
        if( $this->i_maxSize != -1 && ($this->i_fileSize > $this->i_maxSize) ){
            $this->i_error = 5;
            $this->deleteTemp();
            return $this->i_error;
        }
        
        
        
        return parent::doUpload($s_target,$s_newName);
    }
}
?>