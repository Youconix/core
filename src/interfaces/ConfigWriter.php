<?php

interface ConfigWriter
{
  
  /**
   * 
   * @param string $file
   * @throws \RuntimeException
   */
  public function loadConfig($file);
  
  /**
   * Saves the value at the given place
   *
   * @param string $s_path
   *            The path to the language-part
   * @param string $s_content
   *            The content to save
   * @throws \XMLException when the path does not exist
   */
  public function set($s_path, $s_content);

  /**
   * Adds a new node
   *
   * @param string $s_path
   *            The new path
   * @param string $s_content
   *            The new content
   * @throws \XMLException If the path already exists
   */
  public function add($s_path, $s_content);

  /**
   * Checks of the given part of the loaded file exists
   *
   * @param string $s_path
   *            The path to the language-part
   * @return boolean, true if the part exists otherwise false
   */
  public function exists($s_path);

  /**
   * Saves the settings file
   */
  public function save();

  /**
   * Empties the asked part of the loaded file
   *
   * @param string $s_path
   * @throws \XMLException when the path does not exist
   */
  public function emptyPath($s_path);
}
