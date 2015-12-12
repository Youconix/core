<?php
namespace youconix\core\services;

/**
 * File handler class for manipulating files and directorys
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @version 1.0
 * @since 1.0
 */
class File extends Service
{

    /**
     * Starts the service File and loads the exceptions
     *
     * @throws \Exception if the exception directory can not be read
     */
    public function __construct()
    {
        $s_directory = NIV . CORE . 'exceptions';
        $this->readExceptionsDir($s_directory);
        
        $s_directory = NIV . 'includes/exceptions';
        if ($this->exists($s_directory)) {
            $this->readExceptionsDir($s_directory);
        }
    }

    /**
     * Returns if the object schould be treated as singleton
     *
     * @return boolean True if the object is a singleton
     */
    public static function isSingleton()
    {
        return true;
    }

    /**
     * Reads the given exceptions directory
     *
     * @param string $s_directory
     *            directory
     * @throws \Exception if the exception directory can not be read
     */
    protected function readExceptionsDir($s_directory)
    {
        if (! is_readable($s_directory)) {
            throw new \Exception('Can not read directory ' . $s_directory);
        }
        
        /* read directory */
        $s_handle = opendir($s_directory);
        if ($s_handle === false) {
            throw new \Exception('Can not open directory ' . $s_directory);
        }
        
        /* read all exceptions */
        if (file_exists($s_directory . '/GeneralException.inc.php')) {
            require_once ($s_directory . '/GeneralException.inc.php');
        }
        while (false !== ($s_file = readdir($s_handle))) {
            if (($s_file == '.') || ($s_file == '..') || (substr($s_file, - 1) == "~"))
                continue;
                
                /* check of $file is a directory */
            if (is_dir($s_directory . '/' . $s_file) || stripos($s_file, ".php") === false)
                continue;
            
            require_once ($s_directory . '/' . $s_file);
        }
        
        /* close directory */
        closedir($s_handle);
    }

    /**
     * Generates a new file with the given rights
     *
     * @param string $s_file
     *            The url
     * @param int $i_rights
     *            The permissions of the file, default 0644 (read/write for owner, read for rest)
     * @param boolean $bo_binary
     *            true for binary writing, optional
     * @throws \IOException when the given directory is not writable
     */
    public function newFile($s_file, $i_rights, $bo_binary = false)
    {
        \youconix\core\Memory::type('string', $s_file);
        \youconix\core\Memory::type('int', $i_rights);
        
        $s_dir = dirname($s_file);
        
        if (! is_writable($s_dir)) {
            throw new \IOException('Can not make file ' . $s_file . ' in directory ' . $s_dir . '. Check the permissions');
        }
        
        ($bo_binary) ? $s_mode = 'wb+' : $s_mode = 'w+';
        $s_file2 = fopen($s_file, $s_mode);
        fwrite($s_file2, "");
        fclose($s_file2);
        
        $this->rights($s_file, $i_rights);
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
    public function readFile($s_file, $bo_binary = false)
    {
        \youconix\core\Memory::type('string', $s_file);
        
        if (preg_match("#^(http://|ftp://)#si", $s_file)) {
            return $this->readExtenalFile($s_file, $bo_binary);
        }
        
        if (! $this->exists($s_file)) {
            throw new \IOException('File ' . $s_file . ' does not exist!');
        }
        
        /* Check file */
        if (! is_readable($s_file)) {
            throw new \IOException('Can not read ' . $s_file . '. Check the permissionse');
        }
        
        filesize($s_file) != 0 ? $i_size = filesize($s_file) : $i_size = 1;
        ($bo_binary) ? $s_mode = 'rb' : $s_mode = 'r';
        $s_file = fopen($s_file, $s_mode);
        $s_content = fread($s_file, $i_size);
        fclose($s_file);
        
        return $s_content;
    }

    /**
     * Reads the content from the given file on a different server
     *
     * @param string $s_file
     *            name
     * @param boolean $bo_binary
     *            true for binary reading, optional
     * @return string The content from the requested file
     */
    protected function readExtenalFile($s_file, $bo_binary = false)
    {
        $i_size = 50000;
        ($bo_binary) ? $s_mode = 'rb' : $s_mode = 'r';
        $s_file = @fopen($s_file, $s_mode);
        $s_content = @fread($s_file, $i_size);
        @fclose($s_file);
        
        return $s_content;
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
    public function writeFile($s_file, $s_content, $i_rights = 0644, $bo_binary = false)
    {
        \youconix\core\Memory::type('string', $s_file);
        \youconix\core\Memory::type('string', $s_content);
        \youconix\core\Memory::type('int', $i_rights);
        
        /* Check file */
        if (! $this->exists($s_file)) {
            $this->newFile($s_file, $i_rights, $bo_binary);
        }
        
        if (! is_readable($s_file)) {
            throw new \IOException('Can not open file ' . $s_file . '. Check the permissions');
        }
        
        if (! is_writable($s_file)) {
            throw new \IOException('Can not write file ' . $s_file . '. Check the permissions');
        }
        
        ($bo_binary) ? $s_mode = 'wb+' : $s_mode = 'w+';
        $s_file2 = fopen($s_file, $s_mode);
        fwrite($s_file2, $s_content);
        fclose($s_file2);
    }

    /**
     * Writes at the end of the given file or generates it if doens not exists
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
    public function writeLastFile($s_file, $s_content, $i_rights = 0644, $bo_binary = false)
    {
        \youconix\core\Memory::type('string', $s_file);
        \youconix\core\Memory::type('string', $s_content);
        \youconix\core\Memory::type('int', $i_rights);
        
        /* Check file */
        if (! $this->exists($s_file)) {
            $this->newFile($s_file, $i_rights, $bo_binary);
        }
        
        if (! is_readable($s_file)) {
            throw new \IOException('Can not open file ' . $s_file . '. Check the permissions');
        }
        
        if (! is_writable($s_file)) {
            throw new \IOException('Can not write file ' . $s_file . '. Check the permissions');
        }
        
        ($bo_binary) ? $s_mode = 'ab' : $s_mode = 'a';
        $s_file2 = fopen($s_file, $s_mode);
        fwrite($s_file2, $s_content);
        fclose($s_file2);
    }

    /**
     * Writes at the begin of the given file or generates it if doens not exists
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
    public function writeFirstFile($s_file, $s_content, $i_rights = 0644, $bo_binary = false)
    {
        \youconix\core\Memory::type('string', $s_file);
        \youconix\core\Memory::type('string', $s_content);
        \youconix\core\Memory::type('int', $i_rights);
        
        /* Check file */
        if (! $this->exists($s_file)) {
            $this->newFile($s_file, $i_rights);
        }
        
        if (! is_readable($s_file)) {
            throw new \IOException('Can not open file ' . $s_file . '. Check the permissions');
        }
        
        if (! is_writable($s_file)) {
            throw new \IOException('Can not write file ' . $s_file . '. Check the permissions');
        }
        
        $s_contentOld = file_get_contents($s_file);
        
        ($bo_binary) ? $s_mode = 'wb' : $s_mode = 'w';
        $s_file2 = fopen($s_file, $s_mode);
        fwrite($s_file2, $s_content);
        fwrite($s_file2, $s_contentOld);
        fclose($s_file2);
    }

    /**
     * Renames the given file
     *
     * @param string $s_nameOld
     *            The current url
     * @param string $s_nameNew
     *            The new url
     * @throws \IOException when the file does not exist or is not writable (needed for renaming)
     */
    public function renameFile($s_nameOld, $s_nameNew)
    {
        \youconix\core\Memory::type('string', $s_nameOld);
        \youconix\core\Memory::type('string', $s_nameNew);
        
        /* Check file */
        if (! $this->exists($s_nameOld)) {
            throw new \IOException('File ' . $s_nameOld . ' does not exist.');
        }
        
        if (! rename($s_nameOld, $s_nameNew)) {
            throw new \IOException('File ' . $s_nameOld . ' can not be renamed.');
        }
    }

    /**
     * Copy's the given file to the given directory
     *
     * @param string $s_file
     *            The file to copy
     * @param string $s_target
     *            The target directory
     * @throws \IOException when the file is not readable or the target directory is not writable
     */
    public function copyFile($s_file, $s_target)
    {
        \youconix\core\Memory::type('string', $s_file);
        \youconix\core\Memory::type('string', $s_target);
        
        /* Check file */
        if (! $this->exists($s_file)) {
            throw new \IOException('Can not read file ' . $s_file . '.');
        }
        
        /* Check target */
        if (! is_writable($s_target)) {
            throw new \IOException('Can not write in directory ' . $s_target . '.');
        }
        
        $a_filename = explode('/', $s_file);
        $s_filename = end($a_filename);
        
        copy($s_file, $s_target . '/' . $s_filename);
    }

    /**
     * Moves the given file to the given directory
     *
     * @param string $s_file
     *            The current url
     * @param string $s_target
     *            The target directory
     * @throws \IOException when the target directory is not writable (needed for moving)
     */
    public function moveFile($s_file, $s_target)
    {
        \youconix\core\Memory::type('string', $s_file);
        \youconix\core\Memory::type('string', $s_target);
        
        /* Check file and target-directory */
        if (! $this->exists($s_file)) {
            throw new \IOException('File ' . $s_file . ' does not exist');
        }
        
        if (! is_writable($s_file)) {
            throw new \IOException('File ' . $s_file . ' is not writable, needed for deleting.');
        }
        
        if (! is_writable($s_target)) {
            throw new \IOException('Directory ' . $s_target . ' is not writable');
        }
        
        /* Copy old file */
        $this->copyFile($s_file, $s_target);
        
        /* Delete old file */
        $this->deleteFile($s_file);
    }

    /**
     * Deletes the given file
     *
     * @param string $s_file
     *            The file to delete
     * @throws \IOException when the file does not exist or is not writable
     */
    public function deleteFile($s_file)
    {
        \youconix\core\Memory::type('string', $s_file);
        
        /* Check file */
        if (! $this->exists($s_file)) {
            throw new \IOException('File ' . $s_file . ' does not exist.');
        }
        
        if (! is_writable($s_file)) {
            throw new \IOException('File ' . $s_file . ' is not writable.');
        }
        
        unlink($s_file);
    }

    /**
     * Reads the given directory
     *
     * @param string $s_directory
     *            The directory to read
     * @param boolean $bo_recursive
     *            Set true for recursive reading
     * @return array The files and directorys of the directory
     */
    public function readDirectory($s_directory, $bo_recursive = false)
    {
        \youconix\core\Memory::type('string', $s_directory);
        
        /* Check Directory */
        if (! is_readable($s_directory)) {
            throw new \IOException('Can not read directory ' . $s_directory . '.');
        }
        
        /* read directory */
        $s_handle = opendir($s_directory);
        if ($s_handle === false) {
            throw new \IOException('Can not open directory ' . $s_directory . '.');
        }
        
        $a_files = array();
        
        /* read all files */
        while (false !== ($s_file = readdir($s_handle))) {
            if (($s_file == '.') || ($s_file == '..') || (substr($s_file, - 1) == "~"))
                continue;
                
                /* check of $file is a directory */
            if (is_dir($s_directory . '/' . $s_file) && $bo_recursive) {
                /* read subdirectory */
                $a_files[] = $this->readDirectory($s_directory . '/' . $s_file, $bo_recursive);
            } else 
                if ($bo_recursive) {
                    $a_files[] = $s_directory . '/' . $s_file;
                } else {
                    $a_files[] = $s_file;
                }
        }
        
        /* close directory */
        closedir($s_handle);
        
        /* Sort arrays and merge arrays */
        sort($a_files);
        
        return $a_files;
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
    public function newDirectory($s_name, $i_rights = 0755)
    {
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
        
        if (! is_writable($s_dir)) {
            throw new \IOException('Directory ' . $s_name . ' is not writable.');
        }
        
        mkdir($s_name, $i_rights);
        
        $this->rights($s_name, $i_rights);
    }

    /**
     * Renames the given Directory
     *
     * @param string $s_nameOld
     *            The current name
     * @param string $s_nameNew
     *            The new name
     * @throws \IOException if the directory is not writable
     */
    public function renameDirectory($s_nameOld, $s_nameNew)
    {
        \youconix\core\Memory::type('string', $s_nameOld);
        \youconix\core\Memory::type('string', $s_nameNew);
        
        /* Check or the directory exists */
        if (! $this->exists($s_nameOld)) {
            throw new \IOException('Directory ' . $s_nameOld . ' does not exist.');
        }
        
        /* Check for correct rights */
        if (! is_writable($s_nameNew)) {
            throw new \IOException('Can not write new position for directory ' . $s_nameOld . '.');
        }
        
        if ($this->copyDirectory($s_nameOld, $s_nameNew)) {
            $this->deleteDirectory($s_nameOld, $s_nameNew);
        }
    }

    /**
     * Moves a directory too the given address
     *
     * @param string $s_directoryOld
     *            The current directory
     * @param string $s_directoryNew
     *            The target address
     * @return boolean on success, false on failure
     */
    public function moveDirectory($s_directoryOld, $s_directoryNew)
    {
        \youconix\core\Memory::type('string', $s_directoryOld);
        \youconix\core\Memory::type('string', $s_directoryNew);
        
        /* Copy map */
        if ($this->copyDirectory($s_directoryOld, $s_directoryNew)) {
            /* Delete old map */
            return $this->deleteDirectory($s_directoryOld);
        }
    }

    /**
     * Copy's a directory to a new location
     *
     * @param string $s_directoryOld
     *            The current location
     * @param string $s_directoryNew
     *            The new location
     * @throws \IOException if the copy failes
     */
    public function copyDirectory($s_directoryOld, $s_directoryNew)
    {
        \youconix\core\Memory::type('string', $s_directoryOld);
        \youconix\core\Memory::type('string', $s_directoryNew);
        
        /* Check or new map exists */
        if (! $this->exists($s_directoryNew)) {
            $this->newDirectory($s_directoryNew);
        }
        
        $a_dir = explode('/', $s_directoryOld);
        $s_dir = '';
        for ($i = 0; $i < count($a_dir) - 1; $i ++) {
            if ($s_dir == '') {
                $s_dir .= $a_dir[$i];
            } else {
                $s_dir .= '/' . $a_dir[$i];
            }
        }
        
        if (! is_writable($s_dir)) {
            throw new \IOException('Can not write in directory ' . $s_dir . '.');
        }
        
        /* Open old Directory */
        if (! $s_handle = opendir($s_directoryOld)) {
            throw new \IOException('Can not open directory ' . $s_directoryOld . '.');
        }
        
        /* Read old map */
        while (false !== ($s_file = readdir($s_handle))) {
            if (($s_file == '.') || ($s_file == '..') || (substr($s_file, - 1) != "~"))
                continue;
                
                /* check or $file is a map */
            if (! is_dir($s_file)) {
                /* Copy file */
                $this->copyFile($s_directoryOld . '/' . $s_file, $s_directoryNew . '/' . $s_file);
            } else {
                /* Copy subdirectory */
                $this->copyDirectory($s_directoryOld . '/' . $s_file, $s_directoryNew . '/' . $s_file);
            }
        }
    }

    /**
     * Deletes the given Directory
     *
     * @param string $s_directory
     *            The directory to delete
     * @return boolean True on success, false on failure
     * @throws \IOException When the directory is not writetable
     */
    public function deleteDirectory($s_directory)
    {
        \youconix\core\Memory::type('string', $s_directory);
        
        /* Delete all present files and maps */
        if (! $s_handle = opendir($s_directory)) {
            throw new \IOException('Can\'t read directory ' . $s_directory . '.');
        }
        
        while (false !== ($s_file = readdir($s_handle))) {
            if (($s_file != '.') && ($s_file != '..')) {
                /* check or $file is a map */
                if (is_dir($s_directory . '/' . $s_file)) {
                    $this->deleteDirectory($s_directory . $s_file);
                } else {
                    $this->deleteFile($s_directory . '/' . $s_file);
                }
            }
        }
        
        if (! rmdir($s_directory)) {
            throw new \IOException('Can\'t delete ' . $s_directory . '.');
        }
        
        return true;
    }

    /**
     * Checks if the given file or directory exists
     *
     * @param string $s_file
     *            name or directory name
     * @return boolean if file or directory exists, otherwise false
     */
    public function exists($s_file)
    {
        \youconix\core\Memory::type('string', $s_file);
        
        if (file_exists($s_file)) {
            return true;
        }
        
        return false;
    }

    /**
     * Sets the rights from a file or directory.
     * The rights must be in hexadecimal form (0644)
     *
     * @param string $s_file
     *            The file
     * @param int $i_rights
     *            The new rights
     * @return boolean on success, false on failure
     */
    public function rights($s_file, $i_rights)
    {
        \youconix\core\Memory::type('string', $s_file);
        \youconix\core\Memory::type('int', $i_rights);
        
        if (function_exists('chmod')) {
            eval("chmod(\$s_file, $i_rights);");
            
            return true;
        } else {
            return false;
        }
    }
}