<?php
namespace youconix\Core\Common\File;

/**
 * File extension class
 * 
 * @author Rachelle Scheijen
 * @since 2.0
 */
class FileExtension
{

    protected $extension;

    protected $mimetypes;

    protected $description;

    protected $category;

    /**
     * Inits the class FileExtension
     *
     * @param string $extension            
     * @param array $mimetypes            
     * @param string $category            
     * @param string $description            
     */
    public function init($extension, $mimetypes, $category, $description = '')
    {
        if (! is_array($mimetypes)) {
            $mimetypes = [
                $mimetypes
            ];
        }
        
        $this->extension = $extension;
        $this->mimetypes = $mimetypes;
        $this->category = $category;
        $this->description = $description;
    }

    /**
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     *
     * @return array
     */
    public function getMimetypes()
    {
        return $this->mimetypes;
    }

    /**
     * 
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * 
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}