<?php

namespace youconix\Core\Common\File;

/**
 * Image file uploader
 *
 * @author Rachelle Scheijen
 * @since 2.0
 */
class ImageUploadFile extends \youconix\Core\Common\File\UploadFile
{

  protected $maxSize = -1;

  protected $maxHeight = -1;

  protected $maxWidth = -1;

  protected $error;

  /**
   * Constructor
   *
   * @param \LanguageInterface $language
   * @param \youconix\Core\Common\File\DefaultMimetypes $mimetypes
   */
  public function __construct(\LanguageInterface $language, \LoggerInterface $logger, \youconix\Core\Common\Image $file, \youconix\Core\Common\File\DefaultMimetypes $mimetypes)
  {
    parent::__construct($language, $logger, $file);

    $this->setMimeAllowed($mimetypes->jpg);
    $this->setMimeAllowed($mimetypes->jpeg);
    $this->setMimeAllowed($mimetypes->gif);
    $this->setMimeAllowed($mimetypes->png);
  }

  /**
   *
   * @param int $maxSize
   *            The max size in bytes
   */
  public function setMaxSize($maxSize)
  {
    \youconix\core\Memory::type('int', $maxSize);

    $this->maxSize = $maxSize;
  }

  /**
   *
   * @param int $maxHeight
   *            The max height in pixels
   */
  public function setMaxHeigh($maxHeight)
  {
    \youconix\core\Memory::type('int', $maxHeight);

    $this->maxHeight = $maxHeight;
  }

  /**
   *
   * @param int $maxWidth
   *            The max width in pixels
   */
  public function setMaxWidth($maxWidth)
  {
    \youconix\core\Memory::type('int', $maxWidth);

    $this->maxWidth = $maxWidth;
  }

  /**
   * Performs the upload and configurates the uploader
   *
   * @param string $name
   *            The field name
   * @param string $target
   *            The target directory
   * @param string $newName
   *            The new name, optional
   * @param int $maxSize
   *            The maximum image size, optional
   * @param int $maxHeight
   *            The maximum image height, optional
   * @param int $maxWidth
   *            The maximum image width, optional
   * @return int The status code
   *         0 Upload succeeded
   *         -1 Upload failed
   *         -2 File bigger then server limit
   *         -3 File has an invalid extension/mimetype
   *         -4 Moving of the file failed. Check target permissions
   *         -5 Image bigger then set limit
   *         -6 Image bigger then set height or width
   */
  public function upload($name, $target, $newName = '', $maxSize = -1, $maxHeight = -1, $maxWidth = -1)

  {
    $this->setMaxSize($maxSize);
    $this->setMaxHeigh($maxHeight);
    $this->setMaxWidth($maxWidth);

    $this->doUpload($target . $newName);
  }

  /**
   * Performs the upload
   *
   * @param string $target
   *            The target directory
   * @param string $newName
   *            The new name, optional
   * @return int The status code
   *         0 Upload succeeded
   *         -1 Upload failed
   *         -2 File bigger then server limit
   *         -3 File has an invalid extension/mimetype
   *         -4 Moving of the file failed. Check target permissions
   *         -5 Image bigger then set limit
   *         -6 Image bigger then set height or width
   */
  public function doUpload($target, $newName = '')
  {
    if ($this->maxSize != -1 && ($this->fileSize > $this->maxSize)) {
      $this->error = 5;
      $this->deleteTemp();
      return $this->error;
    }

    $i_status = parent::doUpload($target, $newName);
    if ($i_status != 0) {
      return $i_status;
    }

    // Check dimensions
    if ($this->maxHeight != -1 && $this->temp->getHeight() > $this->maxHeight) {
      $this->error = -6;
      $this->deleteTemp();
    }
    if ($this->maxWidth != -1 && $this->temp->getWidth() > $this->maxWidth) {
      $this->error = -6;
      $this->deleteTemp();
    }
    return $this->error;
  }

  /**
   * Returns the translated error message
   *
   * @param int $code
   *            The error code
   * @return string The error message
   */
  public function getErrorMessage($code)
  {
    switch ($code) {
      case 6:
        $s_message = $this->language->get('system/upload_messages/message' . $code);
        $s_message = $this->language->insert($s_message, [
          'max_width',
          'max_height'
        ], [
          $this->maxWidth,
          $this->maxHeight
        ]);
        break;
      default:
        $s_message = parent::getErrorMessage($code);
        break;
    }

    return $s_message;
  }
}