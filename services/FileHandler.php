<?php
namespace core\services;

class FileHandler extends \core\services\Service
{
    public function readDirectory($s_directory){
        \core\Memory::type('string', $s_directory);
        
        return new \DirectoryIterator($s_directory);
    }
    
    public function readRecursiveDirectory($s_directory){
        \core\Memory::type('string', $s_directory);
        
        $directory = new \RecursiveDirectoryIterator($s_directory,\RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directory);
        return $iterator;
    }
    
    public function directoryFilterName(\DirectoryIterator $directory, $a_names = array()){
        return new \core\classes\DirectoryFilterIteractor($directory,$a_names);
    }
    
    public function recursiveDirectoryFilterName(\RecursiveIteratorIterator $directory, $s_filter){
        echo($s_filter);
        return new \RegexIterator($directory, $s_filter,\RegexIterator::MATCH);
    }
    
    public function readFilteredDirectory($s_directory,$a_skipDirs = array(),$s_extension = ''){
        $a_dirs = array();
        $directory = $this->readDirectory($s_directory);
        foreach($directory AS $item){
            if($item->isDot()) continue;
            
            if( in_array($item->getPathname(),$a_skipDirs) )    continue;
                        
            if( $item->isDir() ){
                $a_dirs[$item->getBasename()] = $this->readFilteredDirectory($item->getPathname(),$a_skipDirs,$s_extension);
                continue;
            }
            
            if( !empty($s_extension) && !preg_match('/'.$s_extension.'$/', $item->getBasename())) continue;
            
            $a_dirs[] = clone $item;
        }
        
        return $a_dirs;
    }
    
    /**
     * Reads the content from the given file
     *
     * @param String $s_file
     *            name
     * @param boolean $bo_binary
     *            true for binary reading, optional
     * @return String The content from the requested file
     * @throws IOException when the file does not exists or is not readable
     */
    public function readFile($s_file, $bo_binary = false)
    {
    	\core\Memory::type('string', $s_file,true);
    	
    	if ( !preg_match("#^(http://|ftp://)#si", $s_file) && !$this->exists($s_file)) {
    		throw new \IOException('File ' . $s_file . ' does not exist!');
    	}
    	
    	$file = new \SplFileObject($s_file);
    	if( !$file->isReadable() ){
    		throw new \IOException('Can not read ' . $s_file . '. Check the permissions');
    	}
    	
    	$content = '';
    	while (!$file->eof()) {
    		$content .= $file->fgets();
    	}
    	
    	return $content;
    }
    
    /**
     * Overwrites the given file or generates it if does not exists
     *
     * @param String $s_file
     *            The url
     * @param String $s_content
     *            The content
     * @param int $i_rights
     *            The permissions of the file, default 0644 (read/write for owner, read for rest)
     * @param boolean $bo_binary
     *            true for binary writing, optional
     * @throws IOException When the file is not readable or writable
     */
    public function writeFile($s_file, $s_content, $i_rights = 0644, $bo_binary = false)
    {
    	\core\Memory::type('string', $s_file);
    	\core\Memory::type('string', $s_content);
    	\core\Memory::type('int', $i_rights);
    	
    	$this->writeToFile($s_file, $s_content, 'w',$i_rights);    	
    }
    
    protected function writeToFile($s_file,$s_content,$s_mode,$i_rights){    	
    	$file = new \SplFileObject($s_file,$s_mode);
    	
    	/* Check permissions */
    	if( !$this->exists($s_file) ){
    		$s_dir = dirname($s_file);
    		if (! is_writable($s_dir)) {
    			throw new \IOException('Can not make file ' . $s_file . ' in directory ' . $s_dir . '. Check the permissions');
    		}
    	}
    	else {
    		if (! $file->isReadable() ) {
    			throw new \IOException('Can not open file ' . $s_file . '. Check the permissions');
    		}
    		 
    		if ( ! $file->isWritable() ) {
    			throw new \IOException('Can not write file ' . $s_file . '. Check the permissions');
    		}	
    	}
    	
    	$i_bytes = $file->fwrite($s_content);
    	if( is_null($i_bytes) ){
    		throw new \IOException('Writing to file '.$s_file.' failed.');
    	}
    	unset($file);
    	$this->rights($s_file, $i_rights);
    }
    
    /**
     * Generates a new directory with the given name and rights
     *
     * @param String $s_name
     *            The name
     * @param int $i_rights
     *            The rights, defaul 0755 (write/write/excequte for owner, rest read + excequte)
     * @throws IOException when the target directory is not writable
     */
    public function newDirectory($s_name, $i_rights = 0755)
    {
    	\core\Memory::type('string', $s_name);
    	\core\Memory::type('int', $i_rights);
    
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
     * Checks if the given file or directory exists
     *
     * @param String $s_file
     *            name or directory name
     * @return boolean if file or directory exists, otherwise false
     */
    public function exists($s_file)
    {
        \core\Memory::type('string', $s_file,true);
    
        if (file_exists($s_file)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Sets the rights from a file or directory.
     * The rights must be in hexadecimal form (0644)
     *
     * @param String $s_file
     *            The file
     * @param int $i_rights
     *            The new rights
     * @return boolean on success, false on failure
     */
    public function rights($s_file, $i_rights)
    {
        \core\Memory::type('string', $s_file,true);
        \core\Memory::type('int', $i_rights);
    
        if (function_exists('chmod')) {
            eval("chmod(\$s_file, $i_rights);");
    
            return true;
        } else {
            return false;
        }
    }
}
?>