<?php

namespace youconix\Core\ConfigReaders;


abstract class AbstractConfig
{
  protected $config;

  protected $defaultPrefix = 'settings';

  /**
   * Gives the asked part of the loaded file
   *
   * @param string $path
   *            The path to the language-part
   * @return string The content of the requested part
   * @throws \ConfigException when the path does not exist
   */
  public function get($path)
  {
    if (!$this->exists($path)) {
      throw new \ConfigException('Config value ' . $path . ' does not exist.');
    }

    $path = $this->encodePath($path);

    if (array_key_exists($path, $this->config)) {
      return $this->config[$path];
    }

    return $this->config[$this->defaultPrefix . '.' . $path];
  }

  /**
   * Checks of the given part of the loaded file exists
   *
   * @param string $path
   *            The path to the language-part
   * @return boolean, true if the part exists otherwise false
   */
  public function exists($path)
  {
    $path = $this->encodePath($path);

    return (array_key_exists($path, $this->config) || array_key_exists($this->defaultPrefix . '.' . $path, $this->config));
  }

  /**
   * @param string $path
   * @return string
   */
  protected function encodePath($path)
  {
    $path = str_replace('/', '.', $path);

    return $path;
  }
}