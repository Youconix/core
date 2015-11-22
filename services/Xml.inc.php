<?php
namespace core\services;

/**
 * Xml-handler for parsing XML-files
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @version 1.0
 * @since 1.0
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
class Xml extends Service
{

    protected $dom_document;

    protected $obj_document;

    protected $s_startTag = '';

    /**
     * PHP 5 constructor
     */
    public function __construct()
    {
        $this->dom_document = null;
        $this->obj_document = null;
    }

    /**
     * Creates a new XML-file
     *
     * @param String $s_encoding
     *            The encoding, defautl iso-8859-1
     * @param bool $bo_skipXPath
     *            Set to true to skip loading XPath
     */
    public function createDocument($s_encoding = 'iso-8859-1', $bo_skipXPath = false)
    {
        $this->dom_document = new \DOMDocument('1.0', $s_encoding);
        
        // We don't want to bother with white spaces
        $this->dom_document->preserveWhiteSpace = false;
        
        $this->dom_document->resolveExternals = true; // for character entities
        
        $this->dom_document->formatOutput = true; // keep output alignment
        
        if (! $bo_skipXPath) {
            $this->obj_document = new \DOMXPath($this->dom_document);
        }
    }

    /**
     * Loads the requested XML-file
     *
     * @param String $s_file
     *            The path to the XML-file
     * @param String $s_encoding
     *            The encoding, defautl iso-8859-1
     * @throws IOException when the file does not exist
     */
    public function load($s_file, $s_encoding = 'iso-8859-1')
    {
        $this->createDocument($s_encoding, true);
        
        if (! $this->dom_document->Load($s_file)) {
            throw new \IOException("Can not load XML-file " . $s_file . ". Check the address");
        }
        
        $this->obj_document = new \DOMXPath($this->dom_document);
    }
    
    public function loadXML($s_content,$s_encoding = 'UTF-8'){
        $this->createDocument($s_encoding, true);
        
        if (! $this->dom_document->loadXML($s_content)) {
            throw new \IOException("Can not load XML content. Content may be invalid.");
        }
        
        $this->obj_document = new \DOMXPath($this->dom_document);
    }
    
    public function loadHTML($s_content,$s_encoding = 'UTF-8'){
        $this->createDocument($s_encoding, true);
        
        if (! $this->dom_document->loadHTML($s_content)) {
            throw new \IOException("Can not load HTML content. Content may be invalid.");
        }
        
        $this->obj_document = new \DOMXPath($this->dom_document);
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
        $s_path = $this->getRealPath($s_path);
        
        if (is_null($this->obj_document)) {
            throw new \XMLException("No document loaded.");
        }
        
        $a_return = $this->obj_document->query("//" . $s_path);
        
        if ($a_return->length < 1) {
            /* Part not found */
            throw new \XMLException("Can not find " . $s_path);
        }
        
        foreach ($a_return as $entry) {
            $s_return = $entry->textContent;
        }
        
        return $s_return;
    }

    /**
     * Gives the asked block of the loaded file
     *
     * @param String $s_path
     *            The path to the language-part
     * @return String The content of the requested part
     * @throws XMLException when the path does not exist
     * @return DOMNodeList The block
     */
    public function getBlock($s_path)
    {
        $s_path = $this->getRealPath($s_path);
        
        if (is_null($this->obj_document)) {
            throw new \XMLException("No document loaded.");
        }
        
        $a_return = $this->obj_document->query("//" . $s_path);
        
        if ($a_return->length < 1) {
            /* Part not found */
            throw new \XMLException("Can not find " . $s_path);
        }
        
        return $a_return;
    }

    /**
     * Saves the value at the given place
     *
     * @param String $s_path
     *            The path to the language-part
     * @param String $s_content
     *            The content to save
     * @throws XMLException when the path does not exist
     */
    public function set($s_path, $s_content)
    {
        \core\Memory::type('String', $s_content);
        
        $s_path = $this->getRealPath($s_path);
        
        $a_data = explode('/', $s_path);
        $s_name = end($a_data);
        $a_return = $this->obj_document->query("//" . $s_path);
        
        if ($a_return->length < 1) {
            /* Part not found */
            throw new \XMLException("Can not find " . $s_path);
        }
        $oldnode = $a_return->item(0);
        
        $newNode = $this->dom_document->createElement($s_name);
        $newNode->appendChild($this->dom_document->createCDataSection($s_content));
        $oldnode->parentNode->replaceChild($newNode, $oldnode);
    }

    /**
     * Adds a new node
     *
     * @param string $s_path
     *            The new path
     * @param string $s_content
     *            The new content
     * @throws \XMLException If the path allready exists
     */
    public function add($s_path, $s_content)
    {
        \core\Memory::type('String', $s_path);
        \core\Memory::type('String', $s_content);
        
        if ($this->exists($s_path)) {
            throw new \XMLException("Can not add existing " . $s_path);
        }
        
        if( substr($s_path,0,9) == 'settings/' ){
            $s_path = substr($s_path,9); 
        }
                    
        $s_parent = substr($s_path, 0, strrpos($s_path, '/'));
        $a_path = explode('/', $s_path);
        $s_name = end($a_path);
        
        $this->addBlocks($s_parent);
   
        $element = $this->getBlock($s_parent);
        $element = $element->item(0);
        $node = $this->dom_document->createElement($s_name, $s_content);
        $element->appendChild($node);
    }
    
    protected function addBlocks($s_path){
        $a_path = explode('/', $s_path);
        
        $i_length = count($a_path);
        $s_lastPath = '';
        $s_path = '';
        for($i=0; $i<$i_length; $i++ ){
            $s_lastPath = $s_path;
            
            if( !empty($s_path) ){ $s_path .'/'; }
            $s_path .= $a_path[$i];
            
            try {
                $element = $this->getBlock($s_path);
            }
            catch(\XMLException $e){                
                /* Block does not exist */
                $element = $this->dom_document->createElement($a_path[$i],'');
                $parent = $this->getBlock($s_lastPath);
                
                $parent = $parent->item(0);
                $parent->appendChild($element);
            }
        }
    }

    /**
     * Saves the XML file loaded to the given file
     *
     * @param String $s_file
     *            The filename
     * @throws Exception the directory is not writable
     */
    public function save($s_file)
    {
        \core\Memory::type('String', $s_file);
        
        $s_dir = dirname($s_file);
        
        if (! is_writable($s_dir) && (!file_exists($s_dir) || !is_writable($s_file))) {
            throw new \Exception("Can not write to " . $s_file . '. Check the permissions.');
        }
        
        $this->dom_document->save($s_file);
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
        $s_path = $this->getRealPath($s_path);
        
        $a_return = $this->obj_document->query("//" . $s_path);
        
        if ($a_return->length < 1) {
            /* Part not found */
            return false;
        }
        
        return true;
    }

    /**
     * Replaces the geven keys in the given text with the given values
     *
     * @param String $s_path
     *            The path to the text that need to be changed
     * @param array $a_keys
     *            The keys for the change. Also accepts a string
     * @param array $a_values
     *            The values for the change. Also accepts a string
     * @return String The changed text
     * @throws Exception when the path does not exist
     */
    public function insert($s_path, $a_keys, $a_values)
    {
        $s_text = $this->get($s_path);
        
        if (is_array($a_keys)) {
            for ($i = 0; $i < count($a_keys); $i ++) {
                $s_text = str_replace('[' . $a_keys[$i] . ']', $a_values[$i], $s_text);
            }
        } else {
            $s_text = str_replace('[' . $a_key . ']', $a_values, $s_text);
        }
        
        return $s_text;
    }

    /**
     * Checks the path and adds the default start tag
     *
     * @param String $s_path
     *            path
     * @return String real path
     * @throws XMLException If the path is invalid
     */
    protected function getRealPath($s_path)
    {
        \core\Memory::type('String', $s_path);
        
        if (substr($s_path, - 1) == '/') {
            throw new \XMLException('Invalid XML query : ' . $s_path);
        }
        
        if (empty($this->s_startTag)) {
            return $s_path;
        }
        
        $i_length = strlen($this->s_startTag);
        if (substr($s_path, 0, $i_length) != $this->s_startTag) {
            $s_path = $this->s_startTag . '/' . $s_path;
        }
        
        if( substr($s_path, -1) == '/' ){
            $s_path = substr($s_path, 0,-1);
        }
        
        return $s_path;
    }
}