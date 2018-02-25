<?php

namespace youconix\Core\Services;

class DefaultMimeTypes extends AbstractService
{
  protected $a_extensions = [];

  public function __construct()
  {
    $this->setExtensions();
  }

  protected function setExtensions()
  {
    $this->a_extensions['csv'] = ['text/comma-separated-values', 'text/csv', 'text/plain',
        'application/csv', 'application/excel', 'application/vnd.ms-excel', 'application/vnd.msexcel',
        'text/anytext'];
    $this->a_extensions['jpg'] = ['image/jpg', 'image/jpeg'];
    $this->a_extensions['jpeg'] = ['image/jpeg', 'image/jpg'];
    $this->a_extensions['gif'] = ['image/gif'];
    $this->a_extensions['png'] = ['image/png'];
    $this->a_extensions['tiff'] = ["image/tiff"];
    $this->a_extensions['bmp'] = ["image/bmp"];
    $this->a_extensions['svg'] = ["image/svg", "image/svg+xml"];
    $this->a_extensions['webp'] = ['image/webp'];
  }

  public function __get($s_extension)
  {
    if (!array_key_exists($s_extension, $this->a_extensions)) {
      return 'unkown/unknown';
    }

    return $this->a_extensions[$s_extension];
  }
}
