<?php
namespace youconix\core\services\data;

/**
 * Language-handler for making your website language-independand
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @version 2.0
 * @since 2.0
 */
class LanguageXML extends \youconix\core\services\Language
{

    protected $s_languageFallback;

    protected $s_language = null;

    protected $s_encoding = null;

    /**
     *
     * @var \youconix\core\services\File
     */
    protected $service_File;

    protected $a_documents = array();

    protected $a_documentsFallback = array();

    /**
     *
     * @var \youconix\core\services\Xml
     */
    protected $xml;

    /**
     * PHP 5 constructor
     *
     * @param \youconix\core\services\Xml $xml            
     * @param core\services\File $service_File
     *            The file service
     * @param
     *            \Config The site config
     */
    public function __construct(\youconix\core\services\Xml $xml, \youconix\core\services\File $service_File, \Config $config)
    {
        $this->service_File = $service_File;
        $this->s_startTag = 'language';
        $this->s_language = $config->getLanguage();
        $this->s_languageFallback = $config->getSettings()->get('fallbackLanguage');
        $this->xml = $xml;
        
        $this->a_documents = $this->readLanguage($this->s_language);
        if ($this->s_languageFallback && ! empty($this->s_languageFallback)) {
            $this->a_documentsFallback = $this->readLanguage($this->s_languageFallback);
        }
        
        /* Get encoding */
        $this->s_encoding = $this->get('language/encoding');
    }

    /**
     * Calls the set language-file and reads it
     *
     * @param string $s_language
     *            language code
     * @return array The documents
     * @throws IOException If the system of site language file is missing
     */
    protected function readLanguage($s_language)
    {
        $a_documents = array();
        if ($this->service_File->exists(NIV . 'language/language_' . $s_language . '.lang')) {
            $a_documents['site'] = $this->loadDocument(NIV . 'language/language_' . $this->s_language . '.lang');
            
            return $a_documents;
        }
        
        /* Get files */
        $a_files = $this->service_File->readDirectory(NIV . 'language/' . $s_language . '/LC_MESSAGES');
        foreach ($a_files as $s_file) {
            if (strpos($s_file, '.lang') === false) {
                continue;
            }
            
            $s_name = str_replace('.lang', '', $s_file);
            
            $a_documents[$s_name] = $this->loadDocument(NIV . 'language/' . $s_language . '/LC_MESSAGES/' . $s_file);
            $this->obj_document = null;
        }
        
        if (! array_key_exists('system', $a_documents)) {
            throw new \IOException('Missing system language file for language ' . $s_language . '.');
        }
        if (! array_key_exists('site', $a_documents)) {
            throw new \IOException('Missing site language file for language ' . $s_language . '.');
        }
        
        return $a_documents;
    }

    protected function loadDocument($s_filename)
    {
        $document = clone $this->xml;
        $document->load($s_filename);
        
        return $document;
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
        $obj_file2 = null;
        
        if (! array_key_exists($a_path[0], $this->a_documents)) {
            if (substr($s_path, 0, 8) != 'language') {
                $s_path = 'language/' . $s_path;
            }
            
            $obj_file = $this->a_documents['site'];
            if (array_key_exists('site', $this->a_documentsFallback)) {
                $obj_file2 = $this->a_documentsFallback['site'];
            }
        } else {
            $obj_file = $this->a_documents[$a_path[0]];
            if (array_key_exists($a_path[0], $this->a_documentsFallback)) {
                $obj_file2 = $this->a_documentsFallback[$a_path[0]];
            }
        }
        
        try {
            return $obj_file->get($s_path);
        } catch (\XMLException $e) {
            if (is_null($obj_file2)) {
                throw $e;
            }
            
            return $obj_file2->get($s_path);
        }
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
        \youconix\core\Memory::type('string', $s_text);
        
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
        $obj_file2 = null;
        $a_path = explode('/', $s_path);
        if (! array_key_exists($a_path[0], $this->a_documents)) {
            $obj_file = $this->a_documents['site'];
            if (array_key_exists('site', $this->a_documentsFallback)) {
                $obj_file2 = $this->a_documentsFallback['site'];
            }
        } else {
            $obj_file = $this->a_documents[$a_path[0]];
            if (array_key_exists($a_path[0], $this->a_documentsFallback)) {
                $obj_file2 = $this->a_documentsFallback[$a_path[0]];
            }
        }
        
        if ($obj_file->exists($s_path)) {
            return true;
        }
        if (! is_null($obj_file2) && $obj_file2->exists($s_path)) {
            return true;
        }
        return false;
    }
}