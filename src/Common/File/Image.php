<?php

namespace youconix\Core\Common\File;
/**
 * Image file class
 *
 * @author    Rachelle Scheijen
 * @since     2.0
 * @see \SplFileObject
 */

class Image extends \youconix\Core\Common\File\File
{
  protected $width = null;
  protected $height;
  protected $attr;

  /**
   * @return int
   */
  public function getHeight()
  {
    $this->getSizes();

    return $this->width;
  }

  /**
   * @return int
   */
  public function getWidth()
  {
    $this->getSizes();

    return $this->height;
  }

  /**
   * @return string
   */
  public function getAttribute()
  {
    $this->getSizes();

    return $this->attr;
  }

  /**
   * Collects the data of the image
   */
  protected function getSizes()
  {
    if (is_null($this->width)) {
      list($width, $height, $type, $attr) = getimagesize($this->getRealPath(), FILEINFO_MIME_TYPE);

      $this->width = $width;
      $this->height = $height;
      $this->attr = $attr;
    }
  }
}

?>