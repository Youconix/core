<?php
namespace youconix\core\common\file;

/**
 * Image file uploader
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 2.0
 */
class ImageUploadFile extends \youconix\core\common\file\UploadFile
{

    protected $i_maxSize = - 1;

    protected $i_maxHeight = - 1;

    protected $i_maxWidth = - 1;

    /**
     * Constructor
     *
     * @param \LanguageInterface $language            
     * @param \youconix\core\common\file\DefaultMimetypes $mimetypes            
     */
    public function __construct(\LanguageInterface $language, \LoggerInterface $logger, \youconix\core\common\Image $file, \youconix\core\common\file\DefaultMimetypes $mimetypes)
    {
        parent::__construct($language, $logger, $file);
        
        $this->setMimeAllowed($mimetypes->jpg);
        $this->setMimeAllowed($mimetypes->jpeg);
        $this->setMimeAllowed($mimetypes->gif);
        $this->setMimeAllowed($mimetypes->png);
    }

    /**
     *
     * @param int $i_maxSize
     *            The max size in bytes
     */
    public function setMaxSize($i_maxSize)
    {
        \youconix\core\Memory::type('int', $i_maxSize);
        
        $this->i_maxSize = $i_maxSize;
    }

    /**
     *
     * @param int $i_maxHeight
     *            The max height in pixels
     */
    public function setMaxHeigh($i_maxHeight)
    {
        \youconix\core\Memory::type('int', $i_maxHeight);
        
        $this->i_maxHeight = $i_maxHeight;
    }

    /**
     *
     * @param int $i_maxWidth
     *            The max width in pixels
     */
    public function setMaxWidth($i_maxWidth)
    {
        \youconix\core\Memory::type('int', $i_maxWidth);
        
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
     * @param int $i_maxSize
     *            The maximun image size, optional
     * @param int $i_maxHeigth
     *            The maximun image height, optional
     * @param int $i_maxWidth
     *            The maximun image width, optional
     * @return number The status code
     *         0 Upload succeeded
     *         -1 Upload failed
     *         -2 File bigger then server limit
     *         -3 File has an invalid extension/mimetype
     *         -4 Moving of the file failed. Check target permissions
     *         -5 Image bigger then set limit
     *         -6 Image bigger then set height or width
     */
    public function upload($s_name, $s_target, $s_newName = '', $i_maxSize = -1, $i_maxHeigth = -1, $i_maxWidth = -1)
    
    {
        $this->setMaxSize($i_maxSize);
        $this->setMaxHeigh($i_maxHeight);
        $this->setMaxWidth($i_maxWidth);
        
        $this->doUpload($s_target . $s_newName);
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
    public function doUpload($s_target, $s_newName = '')
    {
        if ($this->i_maxSize != - 1 && ($this->i_fileSize > $this->i_maxSize)) {
            $this->i_error = 5;
            $this->deleteTemp();
            return $this->i_error;
        }
        
        $i_status = parent::doUpload($s_target, $s_newName);
        if ($i_status != 0) {
            return $i_status;
        }
        
        // Check dimensions
        if ($this->i_maxHeight != - 1 && $this->temp->getHeight() > $this->i_maxHeight) {
            $this->i_error = - 6;
            $this->deleteTemp();
        }
        if ($this->i_maxWidth != - 1 && $this->temp->getWidth() > $this->i_maxWidth) {
            $this->i_error = - 6;
            $this->deleteTemp();
        }
        return $this->i_error;
    }

    /**
     * Returns the translated error message
     *
     * @param int $i_code
     *            The error code
     * @return string The error message
     */
    public function getErrorMessage($i_code)
    {
        switch ($i_code) {
            case 6:
                $s_message = $this->language->get('system/upload_messages/message' . $i_code);
                $s_message = $this->language->insert($s_message, array(
                    'max_width',
                    'max_height'
                ), array(
                    $this->i_maxWidth,
                    $this->i_maxHeight
                ));
                break;
            default:
                $s_message = parent::getErrorMessage($i_code);
                break;
        }
        
        return $s_message;
    }
}
?>