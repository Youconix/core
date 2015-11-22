<?php
namespace core\common\file;

class UploadFile extends \File
{

    /**
     *
     * @var \core\services\Language
     */
    protected $language;
    
    /**
     * 
     * @var \Logger
     */
    protected $logger;

    protected $a_mimetypes = array();

    protected $s_fieldName;

    protected $i_error;

    protected $i_maxServerSize;

    protected $i_fileSize;
    
    protected $s_fileExtension;
    protected $s_fileMimetype;
    
    /**
     *
     * @var \core\common\file\File
     */
    protected $file;
    
    /**
     * 
     * @var \core\common\file\File
     */
    protected $temp;

    /**
     * Constructor
     * 
     * @param \core\services\Language $language     The language service
     * @param \Logger $logger                       The logger service
     * @param \core\common\File $file               The file object
     */
    public function __construct(\core\services\Language $language,\Logger $logger,\core\common\File $file)
    {
        $this->language = $language;
        $this->logger = $logger;
        $this->file = $file;
        
        $i_maxUpload = parse_size(ini_get('post_max_size'));
        $i_serverUploadLimit = parse_size(ini_get('upload_max_filesize'));
        
        if ($i_serverUploadLimit > 0 && $i_serverUploadLimit < $i_maxUpload) {
            $i_maxUpload = $i_serverUploadLimit;
        }
        
        $this->i_maxServerSize = $i_maxUpload;
    }

    private function parse_size($size)
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
        $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
        if ($unit) {
            // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        } else {
            return round($size);
        }
    }

    /**
     * Adds an allowed mimetype
     * 
     * @param \core\common\file\FileExtension $allowed      The mimetype
     */
    public function setMimeAllowed(\core\common\file\FileExtension $allowed)
    {
        $this->a_mimetypes[] = $allowed;
    }

    /**
     * Sets the $_FILES field name
     *  
     * @param string $s_name    The name
     */
    public function setFieldName($s_name)
    {
        $this->s_fieldName = $s_name;
    }

    /**
     * Checks if the file is correctly uploaded
     * 
     * @return boolean
     */
    public function isUploaded()
    {
        if( !array_key_exists($this->s_fieldName, $_FILES) || $_FILES[$this->s_fieldName]['error'] != 0){
            $this->i_error = -1;
            return false;
        }
        
        $file = $this->file;
        $this->temp = $file::create($_FILES[$s_name]['tmp_name']);
        $this->i_fileSize = $this->temp->getSize();
        if( $this->i_fileSize > $this->i_maxServerSize ){
            $this->i_error = -2;
            $this->deleteTemp();
            return false;
        }
        
        return true;
    }

    /**
     * Checks if the file is accepted by the server and program
     * 
     * @return boolean
     */
    public function isValid()
    {
        $s_extension = $this->temp->getExtension();
        $this->s_fileExtension = $s_extension;
        if( !array_key_exists($s_extension, $this->a_mimetypes) ){
            $this->i_error = -3;
            $this->deleteTemp();
            
            return false;
        }
        
        $a_mimetype = $this->a_mimetypes->$s_extension;
        $this->s_fileMimetype = $this->temp->getMimetype();
        
        if( !in_array($this->s_fileMimetype, $a_mimetype->getMimetypes()) ){
            $this->i_error = -3;
            $this->deleteTemp();
            return false;
        }
        
        return true;
    }
    
    /**
     * Deletes the temperary file
     */
    protected function deleteTemp(){
        $s_filename = $this->temp->getRealPath();
        $this->temp = null;
        
        unlink($s_filename);
    }

    public function move($s_target, $s_newName = '')
    {
        if (empty($s_newName)) {
            $s_newName = $this->temp->getbasename();
        } 
        else {
            $s_targetName .= $this->s_fileExtension;
        }
        
        if (file_exists($s_target . '/' . $s_targetName)) {
            $i = 1;
            $s_testname = $s_targetName;
            while (file_exists($s_target . '/' . str_replace($s_extension, '__' . $i . $s_extension, $s_testname))) {
                $i ++;
            }
        
            $s_targetName = str_replace($s_extension, '__' . $i . $s_extension, $s_testname);
        }
        
        /* Make sure that the file pointer is free */
        $this->deleteTemp();
        
        /* Move file */
        move_uploaded_file($this->temp->getRealPath(), $s_target . '/' . $s_targetName);
        
        if (! file_exists($s_target . '/' . $s_targetName)) {
            return false;
        }
        
        $this->temp = $file::create($s_target . '/' . $s_targetName);
        
        return true;
    }
    
    public function getFile(){
        return $this->temp;
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
     * @return number The status code
     *         0 Upload succeeded
     *         -1 Upload failed
     *         -2 File bigger then server limit
     *         -3 File has an invalid extension/mimetype
     *         -4 Moving of the file failed. Check target permissions
     */
    public function upload($s_name, $s_target, $s_newName = '')
    {
        $this->setFieldName($s_name);
        
        return $this->doUpload($s_target,$s_newName);
        
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
     */
    public function doUpload($s_target,$s_newName = '')
    {
        if (! $this->isUploaded() || !$this->isValid()  || !$this->move($s_target,$s_newName) ){
            return $this->i_error;
        }
        
        $this->i_error = 0;
        
        return 0;
    }
    
    /**
     * Returns the translated error message
     * 
     * @param int $i_code   The error code
     * @return string   The error message
     */
    public function getErrorMessage($i_code){
        if( !$this->language->exists('system/upload_messages/message'.$i_code) ){
            return '';
        }
        $s_message = $this->language->get('system/upload_messages/message'.$i_code);
            
        
        switch($i_code){
            case 2 :
                $s_message = $this->language->insert($s_message, array('fileSize','serverLimit'),array($this->i_fileSize,$this->i_maxServerSize));
                break;
            case 3 :
                $s_message = $this->language->insert($s_message,array('extension','mimetype','allowed'),
                    array($this->s_fileExtension,$this->s_fileExtension,implode(', ',array_keys($this->a_mimetypes)) ));
                break;
        }
        
        return '';
    }
}
?>