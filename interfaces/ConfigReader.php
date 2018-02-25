<?php

interface ConfigReader
{
  
  /**
   * 
   * @param string $file
   * @throws \RuntimeException
   */
  public function loadConfig($file);

    /**
     * @return array
     */
  public function getConfigAsArray();
  
  /**
   * Gives the asked part of the loaded file
   *
   * @param string $path
   *            The path to the language-part
   * @return string The content of the requested part
   * @throws \ConfigException when the path does not exist
   */
  public function get($path);

  /**
   * Checks of the given part of the loaded file exists
   *
   * @param string $path
   *            The path to the language-part
   * @return boolean, true if the part exists otherwise false
   */
  public function exists($path);
}
