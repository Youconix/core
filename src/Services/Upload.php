<?php
namespace youconix\Core\Services;

/**
 * File upload service
 */
class Upload extends \youconix\Core\Services\AbstractService
{
	/**
   * @var \youconix\core\services\DefaultMimeTypes
   */
  protected $defaultMimeTypes;

  /**
     *
     * @var \youconix\core\services\FileData
     */
    protected $fileData;

    /**
     * PHP 5 constructor
     *          
     * @param \youconix\core\services\FileData $fileData            
	 * @param \youconix\core\services\DefaultMimeTypes $defaultMimeTypes
     */
    public function __construct(\youconix\core\services\FileData $fileData, \youconix\core\services\DefaultMimeTypes $defaultMimeTypes)
    {
        $this->fileData = $fileData;
		$this->defaultMimeTypes = $defaultMimeTypes;
    }

    /**
     * Checks if the file is correct uploaded
     *
     * @param String $s_name
     *            form field name
     * @return boolean if the file is uploaded,otherwise false
     */
    public function isUploaded($s_name)
    {
        return (array_key_exists($s_name, $_FILES) && $_FILES[$s_name]['error'] == 0);
    }

    /**
     * Checks if the file is valid
     *
     * @param String $s_name
     *            field name
     * @param array $a_extensions
     *            extensions with the mimetype as value
     * @param int $i_maxSize
     *            filesize in bytes, optional
     * @return boolean if the file is valid, otherwise false
     */
    public function isValid($s_name, $a_extensions, $i_maxSize = -1)
    {
        $s_mimetype = $this->fileData->getMimeType($_FILES[$s_name]['tmp_name']);
        
        $a_data = explode('/', $s_mimetype);
        $a_fileExtensions = explode('.', $_FILES[$s_name]['name']);
        $s_extension = strtolower(end($a_fileExtensions));
        
        if (! array_key_exists($s_extension, $a_extensions) || ($a_extensions[$s_extension] != $s_mimetype && (is_array($a_extensions[$s_extension]) && ! in_array($s_mimetype, $a_extensions[$s_extension])))) {
            unlink($_FILES[$s_name]['tmp_name']);
            return false;
        }
        
        if ($i_maxSize != - 1 && $_FILES[$s_name]['size'] > $i_maxSize) {
            unlink($_FILES[$s_name]['tmp_name']);
            return false;
        }
        
        return true;
    }

    /**
     * Moves the uploaded file to the target directory
     * Does NOT overwrite files with the same name.
     *
     * @param String $s_name
     *            field name
     * @param String $s_targetDir
     *            directory
     * @param String $s_targetName
     *            name to use. Do not provide an extension, optional. Default the filename
     * @return String choosen filename without directory
     */
    public function moveFile($s_name, $s_targetDir, $s_targetName = '')
    {
        if (empty($s_targetName)) {
            $s_targetName = $_FILES[$s_name]['name'];
        } else {
            $a_fileExtensions = explode('.', $_FILES[$s_name]['name']);
            $s_extension = '.' . strtolower(end($a_fileExtensions));
            
            $s_targetName .= $s_extension;
        }
        
        if (file_exists($s_targetDir . '/' . $s_targetName)) {
            $a_fileExtensions = explode('.', $_FILES[$s_name]['name']);
            $s_extension = '.' . strtolower(end($a_fileExtensions));
            
            $i = 1;
            $s_testname = $s_targetName;
            while (file_exists($s_targetDir . '/' . str_replace($s_extension, '__' . $i . $s_extension, $s_testname))) {
                $i ++;
            }
            
            $s_targetName = str_replace($s_extension, '__' . $i . $s_extension, $s_testname);
        }
        
        move_uploaded_file($_FILES[$s_name]['tmp_name'], $s_targetDir . '/' . $s_targetName);
        
        if (! file_exists($s_targetDir . '/' . $s_targetName)) {
            return '';
        }
        
        return $s_targetName;
    }
	
	public function addHead(\OutputInterface $output)
  {
    $output->append('head',
        '<script src="/js/widgets/fileupload.js" type="text/javascript"></script>');
  }
	
	/**
   * Returns the default mime types
   *
   * @return \youconix\core\services\DefaultMimeTypes
   */
  public function getDefaultMimes(){
    return $this->defaultMimeTypes;
  }
  
  /**
   * @param string $s_filename 
   * @return string
   */
  public function getExtension($s_filename){
	  $s_extension = pathinfo($s_filename, PATHINFO_EXTENSION);
	  return $s_extension;
  }
}
