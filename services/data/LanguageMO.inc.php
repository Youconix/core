<?php
namespace core\services\data;

/**
 * Language-handler for making your website language-independand
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @version 2.0
 * @since 2.0
 *       
 *        Miniature-happiness is free software: you can redistribute it and/or modify
 *        it under the terms of the GNU Lesser General Public License as published by
 *        the Free Software Foundation, either version 3 of the License, or
 *        (at your option) any later version.
 *       
 *        Miniature-happiness is distributed in the hope that it will be useful,
 *        but WITHOUT ANY WARRANTY; without even the implied warranty of
 *        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *        GNU General Public License for more details.
 *       
 *        You should have received a copy of the GNU Lesser General Public License
 *        along with Miniature-happiness. If not, see <http://www.gnu.org/licenses/>.
 */
class LanguageMO extends \core\services\Language
{

    /**
     * 
     * @var \core\services\File
     */
    private $service_File;

    private $s_language = null;
    
    private $s_languageFallback = null;

    protected $a_documents = array();
    
    private $a_documentsFallback = array();

    /**
     * PHP 5 constructor
     *
     * @param \core\services\File $service_File
     *            parser
     * @param string $s_language
     *            language code
     * @param string $s_languageFallback
     * 			   fallback  language code 
     */
    public function __construct(\core\services\File $service_File, $s_language,$s_languageFallback)
    {
        $this->service_File = $service_File;
        $this->s_language = $s_language;
        
        putenv('LC_ALL=' . $s_language);
        setlocale(LC_ALL, $s_language);
        
        $this->a_documents = $this->readLanguages($s_language);
        if( $s_languageFallback && !empty($s_languageFallback) ){
        	$this->a_documentsFallback = $this->readLanguages($s_languageFallback);
        }
        
        /* Get encoding */
        $this->s_encoding = $this->get('language/encoding');
    }

    /**
     * Loads the language files
     * 
     * @param	string	$s_language	The language code
     * @return	array	The documents
     */
    private function readLanguages($s_language)
    {
    	$a_documents = array();
        $a_files = $this->service_File->readDirectory(NIV . 'language/' . $s_language . '/LC_MESSAGES');
        foreach ($a_files as $s_file) {
            if ($s_file == '.' || $s_file == '..' || substr($s_file, - 3) != '.mo') {
                continue;
            }
            
            $s_name = $s_language.'-'.substr($s_file, 0, - 3);
            $a_documents[] = $s_name;
            
            bindtextdomain($s_name, NIV . 'language');
        }
        
        if (! in_array('system', $a_documents)) {
            throw new \IOException('Missing system language file for language ' . $s_language . '.');
        }
        if (! in_array('site', $a_documents)) {
            throw new \IOException('Missing site language file for language ' . $s_language . '.');
        }
        
        return $a_documents;
    }

    /**
     * Gives the asked part of the loaded file
     *
     * @param String $s_path
     *            The path to the language-part
     * @return String The content of the requested part
     * @throws XMLException when the path does not exist
     */
    public function get($s_path)
    {
        $a_path = explode('/', $s_path);
        if (! in_array($this->s_language.'-'.$a_path[0], $this->a_documents)) {
            textdomain('site');
        } else {
            textdomain($this->s_language.'-'.$a_path[0]);
        }
        $s_path = str_replace('/', '_', $s_path);
        
        $s_text = gettext($s_path);
        
        if ($s_text == $s_path) {
        	/* Try fallback */
        	if (! in_array($this->s_languageFallback.'-'.$a_path[0], $this->a_documentsFallback)) {
        		textdomain('site');
        	} else {
        		textdomain($this->s_languageFallback.'-'.$a_path[0]);
        	}
        	$s_path = str_replace('/', '_', $s_path);
        	
        	$s_text = gettext($s_path);
        	
        	if( $s_text == $s_path ){
	            /* Part not found */
	            throw new \XMLException("Can not find " . $s_path);
        	}
        }
        
        return trim($s_text);
    }

    /**
     * Changes the language-values with the given values
     * Collects the text from the language file via the path
     *
     * @param String $s_path
     *            The path to the language-part
     * @param array $a_fields
     *            accepts also a string
     * @param array $a_values
     *            accepts also a string
     * @return string changed language-string
     * @throws XMLException when the path does not exist
     */
    public function insertPath($s_path, $a_fields, $a_values)
    {
        $s_text = $this->get($s_path);
        return $this->insert($s_text, $a_fields, $a_values);
    }

    /**
     * Changes the language-values with the given values
     *
     * @param string $s_text            
     * @param array $a_fields
     *            accepts also a string
     * @param array $a_values
     *            accepts also a string
     * @return string changed language-string
     */
    public function insert($s_text, $a_fields, $a_values)
    {
        \core\Memory::type('string', $s_text);
        
        if (! is_array($a_fields)) {
            $s_text = str_replace('[' . $a_fields . ']', $a_values, $s_text);
        } else {
            for ($i = 0; $i < count($a_fields); $i ++) {
                $s_text = str_replace('[' . $a_fields[$i] . ']', $a_values[$i], $s_text);
            }
        }
        
        return $s_text;
    }

    /**
     * Checks of the given part of the loaded file exists
     *
     * @param String $s_path
     *            The path to the language-part
     * @return boolean, true if the part exists otherwise false
     */
    public function exists($s_path)
    {
        $a_path = explode('/', $s_path);
        if (! in_array($this->s_language.'-'.$a_path[0], $this->a_documents)) {
            textdomain('site');
        } else {
            textdomain($this->s_language.'-'.$a_path[0]);
        }
        $s_path = str_replace('/', '_', $s_path);
        
        $s_text = gettext($s_path);
        
        if ($s_text == $s_path) {
        	/* Try fallback */
        	if (! in_array($this->s_languageFallback.'-'.$a_path[0], $this->a_documentsFallback)) {
        		textdomain('site');
        	} else {
        		textdomain($this->s_languageFallback.'-'.$a_path[0]);
        	}
        	$s_path = str_replace('/', '_', $s_path);
        	
        	$s_text = gettext($s_path);
        	
        	if ($s_text == $s_path) {
	        	/* Part not found */
            	return false;
        	}
        }
        
        return true;
    }
}