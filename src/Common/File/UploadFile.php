<?php

namespace youconix\Core\Common\File;

/**
 * File uploader
 *
 * @author Rachelle Scheijen
 * @since 2.0
 */
class UploadFile extends \File
{

  /**
   *
   * @var \LanguageInterface
   */
  protected $language;

  /**
   *
   * @var \LoggerInterface
   */
  protected $logger;

  protected $mimetypes = [];

  protected $fieldName;

  protected $error;

  protected $maxServerSize;

  protected $fileSize;

  protected $fileExtension;

  protected $fileMimetype;

  /**
   *
   * @var \youconix\Core\Common\File\File
   */
  protected $file;

  /**
   *
   * @var \youconix\Core\Common\File\File
   */
  protected $temp;

  /**
   * Constructor
   *
   * @param \LanguageInterface $language
   * @param \LoggerInterface $logger
   * @param \youconix\Core\Common\File $file
   */
  public function __construct(\LanguageInterface $language, \LoggerInterface $logger, \youconix\Core\Common\File $file)
  {
    $this->language = $language;
    $this->logger = $logger;
    $this->file = $file;

    $maxUpload = $this->parse_size(ini_get('post_max_size'));
    $serverUploadLimit = $this->parse_size(ini_get('upload_max_filesize'));

    if ($serverUploadLimit > 0 && $serverUploadLimit < $maxUpload) {
      $maxUpload = $serverUploadLimit;
    }

    $this->maxServerSize = $maxUpload;
  }

  /**
   * Parses the max server upload size
   *
   * @param string $size
   * @return int The max server upload size in bytes
   */
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
   * @param \youconix\Core\Common\File\FileExtension $allowed
   *            The mimetype
   */
  public function setMimeAllowed(\youconix\Core\Common\File\FileExtension $allowed)
  {
    $this->mimetypes[] = $allowed;
  }

  /**
   * Sets the $_FILES field name
   *
   * @param string $name
   */
  public function setFieldName($name)
  {
    $this->fieldName = $name;
  }

  /**
   * Checks if the file is correctly uploaded
   *
   * @return boolean
   */
  public function isUploaded()
  {
    if (!array_key_exists($this->fieldName, $_FILES) || $_FILES[$this->fieldName]['error'] != 0) {
      $this->error = -1;
      return false;
    }

    $file = $this->file;
    $this->temp = $file::create($_FILES[$this->fileName]['tmp_name']);
    $this->fileSize = $this->temp->getSize();
    if ($this->fileSize > $this->maxServerSize) {
      $this->error = -2;
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
    $extension = $this->temp->getExtension();
    $this->fileExtension = $extension;
    if (!array_key_exists($extension, $this->mimetypes)) {
      $this->error = -3;
      $this->deleteTemp();

      return false;
    }

    $mimetype = $this->mimetypes->$extension;
    $this->fileMimetype = $this->temp->getMimetype();

    if (!in_array($this->fileMimetype, $mimetype->getMimetypes())) {
      $this->error = -3;
      $this->deleteTemp();
      return false;
    }

    return true;
  }

  /**
   * Deletes the temporary file
   */
  protected function deleteTemp()
  {
    $filename = $this->temp->getRealPath();
    $this->temp = null;

    unlink($filename);
  }

  /**
   * Moves the uploaded file
   *
   * @param string $target
   * @param string $newName
   * @return boolean
   */
  public function move($target, $newName = '')
  {
    if (empty($newName)) {
      $newName = $this->temp->getbasename();
    } else {
      $newName .= $this->fileExtension;
    }

    if (file_exists($target . '/' . $newName)) {
      $i = 1;
      $testname = $newName;
      while (file_exists($target . '/' . str_replace($this->fileExtension, '__' . $i . $this->fileExtension, $testname))) {
        $i++;
      }

      $newName = str_replace($this->fileExtension, '__' . $i . $this->fileExtension, $newName);
    }

    /* Make sure that the file pointer is free */
    $this->deleteTemp();

    /* Move file */
    move_uploaded_file($this->temp->getRealPath(), $target . '/' . $newName);

    if (!file_exists($target . '/' . $newName)) {
      return false;
    }

    $file = $this->file;
    $this->temp = $file::create($target . '/' . $newName);

    return true;
  }

  /**
   * Returns the uploaded file
   *
   * @return \youconix\Core\Common\File\File
   */
  public function getFile()
  {
    return $this->temp;
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
   * @return int The status code
   *         0 Upload succeeded
   *         -1 Upload failed
   *         -2 File bigger then server limit
   *         -3 File has an invalid extension/mimetype
   *         -4 Moving of the file failed. Check target permissions
   */
  public function upload($name, $target, $newName = '')
  {
    $this->setFieldName($name);

    return $this->doUpload($target, $newName);
  }

  /**
   * Performs the upload
   *
   * @param string $target
   *            The target directory
   * @param string $newName
   *            The new name, optional
   * @return number The status code
   *         0 Upload succeeded
   *         -1 Upload failed
   *         -2 File bigger then server limit
   *         -3 File has an invalid extension/mimetype
   *         -4 Moving of the file failed. Check target permissions
   */
  public function doUpload($target, $newName = '')
  {
    if (!$this->isUploaded() || !$this->isValid() || !$this->move($target, $newName)) {
      return $this->error;
    }

    $this->error = 0;

    return 0;
  }

  /**
   * Returns the translated error message
   *
   * @param int $code
   * @return string The error message
   */
  public function getErrorMessage($code)
  {
    if (!$this->language->exists('system/upload_messages/message' . $code)) {
      return '';
    }
    $message = $this->language->get('system/upload_messages/message' . $code);

    switch ($code) {
      case 2:
        $message = $this->language->insert($message, [
          'fileSize',
          'serverLimit'
        ], [
          $this->fileSize,
          $this->maxServerSize
        ]);
        break;
      case 3:
        $message = $this->language->insert($message, [
          'extension',
          'mimetype',
          'allowed'
        ], [
          $this->fileExtension,
          $this->fileExtension,
          implode(', ', array_keys($this->mimetypes))
        ]);
        break;
    }

    return $message;
  }
}