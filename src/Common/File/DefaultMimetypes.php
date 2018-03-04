<?php

namespace youconix\Core\Common\File;

/**
 * Default mime types
 *
 * @author Rachelle Scheijen
 * @since 2.0
 */
class DefaultMimetypes
{

  /**
   *
   * @var \youconix\Core\Common\File\FileExtension
   */
  protected $fileExtension;

  protected $mimetypes = [];

  /**
   * Constructor
   *
   * @param \youconix\Core\Common\File\FileExtension $fileExtension
   */
  public function __construct(\youconix\Core\Common\File\FileExtension $fileExtension)
  {
    $this->fileExtension = $fileExtension;

    $this->setMimetypes();
  }

  /**
   * Sets the default mime types
   */
  protected function setMimetypes()
  {
    // Images
    $this->createMimetype('gif', 'image/gif', 'image', 'GIF image');
    $this->createMimetype('jpg', 'image/jpg', 'image', 'JPEG image');
    $this->createMimetype('jpeg', 'image/jpeg', 'image', 'JPEG image');
    $this->createMimetype('png', 'image/png', 'image', 'PNG image');
    $this->createMimetype('bmp', 'image/bmp', 'image', 'BMP image');
    $this->createMimetype('tiff', 'image/tiff', 'image', 'TIFF image');
    $this->createMimetype('svg', array(
      'image/svg',
      'image/svg+xml'
    ), 'image', 'SVG image');
    $this->createMimetype('webp', 'image/webp', 'image', 'WEBP image');
    $this->createMimetype('ico', 'image/x-icon', 'Ico image');

    // Video
    $this->createMimetype('ogg', 'application/ogg', 'Video');
    $this->createMimetype('ogv', 'video/ogg', 'Video');
    $this->createMimetype('mp4', 'video/mp4', 'Video');
    $this->createMimetype('webm', 'video/webm', 'Video');
    $this->createMimetype('avi', 'video/avi', 'Video');

    // Audio
    $this->createMimetype('oga', 'audio/ogg', 'Audio');
    $this->createMimetype('mp3', 'audio/mpeg', 'Audio');
    $this->createMimetype('wav', 'audio/wav', 'Audio');

    // General files
    $this->createMimetype('txt', 'text/plain', 'General');
    $this->createMimetype('text', 'text/plain', 'General');
    $this->createMimetype('html', 'text/html', 'General');
    $this->createMimetype('css', 'text/css', 'General');
    $this->createMimetype('js', array(
      'application/javascript',
      'application/ecmascript',
      'text/javascript'
    ), 'General');
    $this->createMimetype('sprite', 'application/x-sprite', 'General');

    // Archives
    $this->createMimetype('bz', 'application/x-bzip', 'Archive');
    $this->createMimetype('bz2', 'application/x-bzip2', 'Archive');
    $this->createMimetype('gz', 'application/x-gzip', 'Archive');
    $this->createMimetype('gzip', 'application/x-gzip', 'Archive');
    $this->createMimetype('zip', 'application/zip', 'Archive');
    $this->createMimetype('gzip', 'application/x-gzip', 'Archive');
    $this->createMimetype('tar', 'application/x-tar', 'Archive');
    $this->createMimetype('rar', 'application/x-rar', 'Archive');
  }

  /**
   * Creates a mime type
   *
   * @param string $s_extension
   * @param array $mimetypes
   * @param string $s_category
   * @param string $s_description
   */
  protected function createMimetype($s_extension, $mimetypes, $s_category, $s_description = '')
  {
    $extension = clone $this->fileExtension;

    $extension->init($s_extension, $mimetypes, $s_category, $s_description);
    $this->mimetypes[$s_extension] = $extension;
  }

  /**
   * Magic getter
   *
   * @param string $s_name
   * @return mixed|NULL
   */
  public function __get($s_name)
  {
    if (array_key_exists($s_name, $this->mimetypes)) {
      return $this->mimetypes[$s_name];
    }

    $trace = debug_backtrace();
    trigger_error('Undefined property via __get(): ' . $s_name . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_NOTICE);
    return null;
  }
}