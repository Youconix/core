<?php
namespace youconix\core\common\file;

/**
 * File extension class
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 2.0
 */
class FileExtension
{

    protected $s_extension;

    protected $a_mimetypes;

    protected $s_description;

    protected $s_category;

    /**
     * Inits the class FileExtension
     *
     * @param string $s_extension            
     * @param array $a_mimetypes            
     * @param string $s_category            
     * @param string $s_description            
     */
    public function init($s_extension, $a_mimetypes, $s_category, $s_description = '')
    {
        if (! is_array($a_mimetypes)) {
            $a_mimetypes = array(
                $a_mimetypes
            );
        }
        
        $this->s_extension = $s_extension;
        $this->a_mimetypes = $a_mimetypes;
        $this->s_category = $s_category;
        $this->s_description = $s_description;
    }

    /**
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->s_extension;
    }

    /**
     *
     * @return array
     */
    public function getMimetypes()
    {
        return $this->a_mimetypes;
    }

    /**
     * 
     * @return string
     */
    public function getCategory()
    {
        return $this->s_category;
    }

    /**
     * 
     * @return string
     */
    public function getDescription()
    {
        return $this->s_description;
    }
}