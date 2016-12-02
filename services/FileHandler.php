<?php

namespace youconix\core\services;

class FileHandler extends \youconix\core\services\Service {

  /**
   * Reads the directory
   *
   * @param string $s_directory
   * @return \DirectoryIterator
   */
  public function readDirectory($s_directory) {
    \youconix\core\Memory::type('string', $s_directory);

    return new \DirectoryIterator($s_directory);
  }

  /**
   * Reads the directory recursive
   *
   * @param string $s_directory
   * @return \RecursiveIteratorIterator
   */
  public function readRecursiveDirectory($s_directory) {
    \youconix\core\Memory::type('string', $s_directory);

    $directory = new \RecursiveDirectoryIterator($s_directory, \RecursiveDirectoryIterator::SKIP_DOTS);
    $iterator = new \RecursiveIteratorIterator($directory,\RecursiveIteratorIterator::CHILD_FIRST);
    return $iterator;
  }

  /**
   * Creates a directory filter
   *
   * @param \DirectoryIterator $directory
   * @param array $a_names
   * @return \youconix\core\classes\DirectoryFilterIteractor
   */
  public function directoryFilterName(\DirectoryIterator $directory, $a_names = array()) {
    return new \youconix\core\classes\DirectoryFilterIteractor($directory, $a_names);
  }

  /**
   * Creates a recursive directory filter
   *
   * @param \RecursiveIteratorIterator $directory
   * @param array $a_names
   * @return \RegexIterator
   */
  public function recursiveDirectoryFilterName(\RecursiveIteratorIterator $directory, $s_filter) {
    return new \RegexIterator($directory, $s_filter, \RegexIterator::MATCH);
  }

  /**
   * Reads the directory filtered
   * 
   * @param string $s_directory
   * @param array $a_skipDirs
   * @param string $s_extension
   * @return \DirectoryIterator[]
   */
  public function readFilteredDirectory($s_directory, $a_skipDirs = array(), $s_extension = '') {
    $a_dirs = array();
    $directory = $this->readDirectory($s_directory);
    foreach ($directory as $item) {
      if ($item->isDot())
	continue;

      if (in_array($item->getPathname(), $a_skipDirs))
	continue;

      if ($item->isDir()) {
	$a_dirs[$item->getBasename()] = $this->readFilteredDirectory($item->getPathname(), $a_skipDirs, $s_extension);
	continue;
      }

      if (!empty($s_extension) && !preg_match('/' . $s_extension . '$/', $item->getBasename()))
	continue;

      $a_dirs[] = clone $item;
    }

    return $a_dirs;
  }

  /**
   * Deletes the content of a directory
   *
   * @param string $s_directory
   */
  public function deleteDirectoryContent($s_directory){
    $iterator = $this->readRecursiveDirectory($s_directory);
    foreach( $iterator AS $item ){
      if( $item->isDir() ){
        $this->deleteDirectory($item->getPathname());
      }
      else {
        unlink($item->getPathname());
      }
    }
  }

  /**
   * Deletes the content of a directory and the directory itself
   *
   * @param string $s_directory
   */
  public function deleteDirectory($s_directory){
    $this->deleteDirectoryContent($s_directory);

    rmdir($s_directory);
  }

  /**
   * Returns the requested file
   *
   * @param string $s_file
   * @return \SplFileObject
   * @throws \IOException if the file does not exist or is not readable
   */
  public function getFile($s_file) {
    \youconix\core\Memory::type('string', $s_file, true);

    if (!preg_match("#^(http://|ftp://)#si", $s_file) && !$this->exists($s_file)) {
      throw new \IOException('File ' . $s_file . ' does not exist!');
    }

    $file = new \SplFileObject($s_file);
    if (!$file->isReadable()) {
      throw new \IOException('Can not read ' . $s_file . '. Check the permissions');
    }
    
    return $file;
  }

  /**
   * Reads the content of a file
   *
   * @param \SplFileObject $file
   * @return string
   */
  public function readFileObject(\SplFileObject $file){
    $content = '';
    while (!$file->eof()) {
      $content .= $file->fgets();
    }

    return $content;
  }

  /**
   * Reads the content from the given file
   *
   * @param string $s_file
   *            name
   * @param boolean $bo_binary
   *            true for binary reading, optional
   * @return string The content from the requested file
   * @throws \IOException when the file does not exists or is not readable
   */
  public function readFile($s_file, $bo_binary = false) {
    \youconix\core\Memory::type('string', $s_file, true);

    $file = $this->getFile($s_file);
    return $this->readFileObject($file);
  }

  /**
   * Overwrites the given file or generates it if does not exists
   *
   * @param string $s_file
   *            The url
   * @param string $s_content
   *            The content
   * @param int $i_rights
   *            The permissions of the file, default 0644 (read/write for owner, read for rest)
   * @param boolean $bo_binary
   *            true for binary writing, optional
   * @throws \IOException When the file is not readable or writable
   */
  public function writeFile($s_file, $s_content, $i_rights = 0644, $bo_binary = false) {
    \youconix\core\Memory::type('string', $s_file);
    \youconix\core\Memory::type('string', $s_content);
    \youconix\core\Memory::type('int', $i_rights);

    $this->writeToFile($s_file, $s_content, 'w', $i_rights);
  }

  /**
   * Overwrites the given file or generates it if does not exists
   *
   * @param string $s_file
   *            The url
   * @param string $s_content
   *            The content
   * @param string  $s_mode   r for reading, w for writing
   * @param int $i_rights
   *            The permissions of the file, default 0644 (read/write for owner, read for rest)
   * @throws \IOException When the file is not readable or writable
   */
  protected function writeToFile($s_file, $s_content, $s_mode, $i_rights) {
    try {
      $file = new \SplFileObject($s_file, $s_mode);

      /* Check permissions */
      if (!$this->exists($s_file)) {
        $s_dir = dirname($s_file);
        if (!is_writable($s_dir)) {
          throw new \IOException('Can not make file ' . $s_file . ' in directory ' . $s_dir . '. Check the permissions');
        }
      } else {
        if (!$file->isReadable()) {
          throw new \IOException('Can not open file ' . $s_file . '. Check the permissions');
        }

        if (!$file->isWritable()) {
          throw new \IOException('Can not write file ' . $s_file . '. Check the permissions');
        }
      }

      $i_bytes = $file->fwrite($s_content);
      if (is_null($i_bytes)) {
        throw new \IOException('Writing to file ' . $s_file . ' failed.');
      }
      unset($file);
      $this->rights($s_file, $i_rights);
    }
    catch(\Exception $e){
      throw new \IOException('Can not open file '.$s_file.' in mode '.$s_mode.'.');
    }
  }

  /**
   * Deletes the given file
   *
   * @param string $s_file
   * @throws \IOException If the file does not exist
   */
  public function deleteFile($s_file){
    if( !$this->exists($s_file) ){
      throw new \IOException('Can not remove non existing file '.$s_file);
    }

    unlink($s_file);
  }

  /**
   * Generates a new directory with the given name and rights
   *
   * @param string $s_name
   *            The name
   * @param int $i_rights
   *            The rights, defaul 0755 (write/write/excequte for owner, rest read + excequte)
   * @throws \IOException when the target directory is not writable
   */
  public function newDirectory($s_name, $i_rights = 0755) {
    \youconix\core\Memory::type('string', $s_name);
    \youconix\core\Memory::type('int', $i_rights);

    $s_dir = $s_name;
    if (substr($s_dir, - 1) == '/')
      $s_dir = substr($s_dir, 0, - 1);
    $i_pos = strrpos($s_dir, '/');
    if ($i_pos === false) {
      throw new \IOException("Invalid directory " . $s_name . ".");
    }

    $s_dir = substr($s_dir, 0, $i_pos);

    if (!is_writable($s_dir)) {
      throw new \IOException('Directory ' . $s_name . ' is not writable.');
    }

    mkdir($s_name, $i_rights);

    $this->rights($s_name, $i_rights);
  }

  /**
   * Checks if the given file or directory exists
   *
   * @param string $s_file
   *            name or directory name
   * @return boolean if file or directory exists, otherwise false
   */
  public function exists($s_file) {
    \youconix\core\Memory::type('string', $s_file, true);

    if (file_exists($s_file)) {
      return true;
    }

    return false;
  }

  /**
   * Checks if the file or directory is readable
   *
   * @param string $s_file
   * @return boolean
   */
  public function isReadable($s_file) {
    $file = new \SplFileObject($s_file);
    return ($file->isReadable());
  }

  /**
   * Sets the rights from a file or directory.
   * The rights must be in hexadecimal form (0644)
   *
   * This functies does NOT work on Windows!
   *
   * @param string $s_file
   *            The file
   * @param int $i_rights
   *            The new rights
   * @return boolean on success, false on failure
   */
  public function rights($s_file, $i_rights) {
    \youconix\core\Memory::type('string', $s_file, true);
    \youconix\core\Memory::type('int', $i_rights);

    if (function_exists('chmod')) {
      chmod($s_file, $i_rights);

      return true;
    } else {
      return false;
    }
  }

}

?>