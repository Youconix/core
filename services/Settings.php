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
class Settings implements \Settings
{

  protected $s_settingsDir;

  /**
   *
   * @var \ConfigReader $configReader
   */
  protected $configReader;

  /**
   * @param \ConfigReader $configReader
   */
  public function __construct(\ConfigReader $configReader)
  {
    $this->configReader = $configReader;
    try {
      $this->configReader->loadConfig('settings');
    }
    catch(\RuntimeException $e){
      $s_base = \youconix\core\Memory::detectBase();

      \youconix\core\Memory::redirect($s_base . '/install/');
      exit();
    }
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
  public function save($file = '')
  {
    $this->configReader->save($this->s_settingsDir . '/settings.xml');
  }

  /**
   * Adds a new node
   *
   * @param string $path
   * @param string $content
   * @throws \XMLException If the path already exists
   */
  public function add($path, $content)
  {
    $this->configReader->add($path, $content);
  }

  /**
   * Checks of the given part of the loaded file exists
   *
   * @param string $path
   * @return boolean, true if the part exists otherwise false
   */
  public function exists($path)
  {
    return $this->configReader->exists($path);
  }

  /**
   * Gives the asked part of the loaded file
   *
   * @param string $path
   * @return string The content of the requested part
   * @throws \XMLException when the path does not exist
   */
  public function get($path)
  {
    return $this->configReader->get($path);
  }

  /**
   * Saves the value at the given place
   *
   * @param string $path
   * @param string $content
   * @throws \XMLException when the path does not exist
   */
  public function set($path, $content)
  {
    return $this->configReader->set($path, $content);
  }

  /**
   * Empties the asked part of the loaded file
   *
   * @param string $path
   * @throws \XMLException when the path does not exist
   */
  public function emptyPath($path)
  {
    $this->configReader->emptyPath($path);
  }

  /**
   * Gives the asked block of the loaded file
   *
   * @param string $path
   * @return string The content of the requested part
   * @throws \XMLException when the path does not exist
   * @return array The block
   */
  public function getBlock($path)
  {
    return $this->configReader->getBlock($path);
  }

  public function loadConfig($file)
  {
    $this->configReader->loadConfig($file);
  }
}
