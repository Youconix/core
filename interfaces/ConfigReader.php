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
   * Gives the asked part of the loaded file
   *
   * @param string $s_path
   *            The path to the language-part
   * @return string The content of the requested part
   * @throws \XMLException when the path does not exist
   */
  public function get($s_path);
  
  /**
   * Gives the asked block of the loaded file
   *
   * @param string $s_path
   *            The path to the language-part
   * @return string The content of the requested part
   * @throws \XMLException when the path does not exist
   * @return \DOMNodeList The block
   */
  public function getBlock($s_path);

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
   * @param string $file
   */
  public function save($file);

  /**
   * Empties the asked part of the loaded file
   *
   * @param string $s_path
   * @throws \XMLException when the path does not exist
   */
  public function emptyPath($s_path);
}
