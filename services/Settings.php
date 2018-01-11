<?php

namespace youconix\core\services;

/**
 * Settings handler.
 * This class contains all the framework settings.
 * The settings file is stored in de settings directory in de data dir (default admin/data)
 *
 * @version 1.0
 * @see core/services/Xml.inc.php
 * @since 1.0
 */
class Settings extends \youconix\core\services\Xml implements \Settings
{

  protected $s_settingsDir;

  /**
   * PHP 5 constructor
   */
  public function __construct()
  {
    parent::__construct();

    $this->s_settingsDir = DATA_DIR . 'settings';

    if (file_exists($this->s_settingsDir . '/settings.xml')) {
      $this->load($this->s_settingsDir . '/settings.xml');
    } else {
      $s_base = \youconix\core\Memory::detectBase();

      \youconix\core\Memory::redirect($s_base . '/install/');
      exit();
    }

    $this->s_startTag = 'settings';
  }

  /**
   * Returns if the object should be treated as singleton
   *
   * @return boolean
   */
  public static function isSingleton()
  {
    return true;
  }

  /**
   * Saves the settings file
   */
  public function save($s_file = '')
  {
    parent::save($this->s_settingsDir . '/settings.xml');
  }
}
