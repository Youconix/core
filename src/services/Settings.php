<?php

namespace youconix\core\services;

use youconix\core\services\FileHandler;
use youconix\core\ConfigReaders\XmlConfigReader;
use youconix\core\ConfigReaders\YamlConfigReader;

/**
 * Settings handler.
 * This class contains all the framework settings.
 * The settings files are stored in de settings directory in de data dir (default admin/data)
 *
 * @version 2.0
 * @since 1.0
 */
class Settings extends \youconix\core\ConfigReaders\AbstractConfig implements \Settings
{

    /** @var \youconix\core\ConfigReaders\XmlConfigReader $xmlReader */
    protected $xmlConfigReader;

    /** @var \youconix\core\ConfigReaders\YamlConfigReader $xmlReader */
    protected $yamlConfigReader;

    /** @var \youconix\core\services\FileHandler $file */
    protected $file;

    /** @var array */
    protected $config = [];

    /** @var array  */
    protected $blocks = [];

    /** @var array */
    protected $files;

    /**
     * @param FileHandler $file
     * @param XmlConfigReader $xmlReader
     * @param YamlConfigReader $yamlReader
     */
    public function __construct(FileHandler $file, XmlConfigReader $xmlReader, YamlConfigReader $yamlReader)
    {
        $this->xmlConfigReader = $xmlReader;
        $this->yamlConfigReader = $yamlReader;
        $this->file = $file;

        $this->readCacheFiles();

        if (!array_key_exists('settings.main.nameSite', $this->config)) {
            //\youconix\Core\Memory::redirect('/router.php/install/');
            exit();
        }
    }

    protected function readCacheFiles()
    {
        $cacheFile = $this->createCacheFileName();

        if (!defined('DEBUG') && $this->file->exists($cacheFile)) {
            $content = $this->file->readFile($cacheFile);
            $this->config = unserialize($content);
            return;
        }

        $filesXml = $this->file->readFilteredDirectoryNames(DATA_DIR.DS.'settings', [], 'xml');
        $filesYaml = array_merge(
            $this->file->readFilteredDirectoryNames(DATA_DIR.DS.'settings', [], 'yml'),
            $this->file->readFilteredDirectoryNames(DATA_DIR.DS.'settings', [], 'yaml')
        );

        $this->files = [];
        $this->parseDirectory($filesXml, 'xml');
        $this->parseDirectory($filesYaml, 'yaml');

        foreach($this->files as $file){
            if ($file['type'] == 'xml'){
                $this->xmlConfigReader->loadConfig($file['file']);
                $config = $this->xmlConfigReader->getConfigAsArray();
            }
            else {
                $this->yamlConfigReader->loadConfig($file['file']);
                $config = $this->yamlConfigReader->getConfigAsArray();
            }

            $this->config = array_merge($this->config, $config);
        }

        $this->file->writeFile($cacheFile, serialize($this->config));
    }

    /**
     * @param array $files
     * @param string $type
     */
    protected function parseDirectory(array $files, $type)
    {
        foreach($files as $file){
            if (is_array($file)) {
                $this->parseDirectory($file, $type);
                continue;
            }

            $parts = explode(DS, substr($file,0,strrpos($file,'.')));
            $this->files[end($parts)] = [
                'file' => $file,
                'type' => $type
            ];
        }
    }

    /**
     * Returns if the object should be treated as singleton
     *
     * @return boolean
     */
    public static function isSingleton()
    {
        return true;
    }

    /**
     * @return string
     */
    protected function createCacheFileName()
    {
        return DATA_DIR . DS . 'settings' . DS . 'config_cache.php';
    }


    /**
     * Gives the asked config block
     *
     * @param string $path
     * @return string The content of the requested part
     * @throws \ConfigException
     * @return array The block
     */
    public function getBlock($path)
    {
        $path = $this->encodePath($path);

        if (in_array($path, $this->blocks)) {
            return $this->blocks[$path];
        }

        $search = $path.'.';
        $length = strlen($search);
        $block = [];
        foreach($this->config as $key => $value){
            if (substr($key,0,$length) != $search) {
                continue;
            }

            $key = str_replace($search, '', $key);
            $block[$key] = $value;
        }

        if (count($block) == 0){
            throw new \ConfigException('Call to unknown config block '.$path.'.');
        }

        return $block;
    }
}
