<?php
namespace youconix\core;

define('PROCESS', 'true');
define('NIV', '../../../');

require_once (NIV . 'vendor/youconix/core/bootstrap.php');

class Combiner
{

    /**
     *
     * @var \Config
     */
    protected $config;

    /**
     *
     * @var \Headers
     */
    protected $headers;

    /**
     *
     * @var \youconix\core\services\FileHandler
     */
    protected $file;

    private $a_types = array(
        'javascript',
        'css'
    );

    private $s_file = "";

    public function __construct(\Config $config, \youconix\core\services\FileHandler $file, \youconix\core\services\Headers $headers)
    {
        $this->config = $config;
        $this->file = $file;
        $this->headers = $headers;
        
        $this->init();
        
        $this->parse();
    }

    private function init()
    {
        if (! isset($_GET['type']) || ! in_array($_GET['type'], $this->a_types) || ! isset($_GET['files'])) {
            $this->headers->http400();
            $this->headers->printHeaders();
            exit();
        }
    }

    private function parse()
    {
        switch ($_GET['type']) {
            case 'javascript':
                $this->s_file = '"use strict";' . "\n";
                $this->parseFiles('js');
                $this->headers->setJavascript();
                break;
            case 'css':
                $this->parseFiles('css');
                $this->headers->setCSS();
                break;
        }
        
        $this->headers->printHeaders();
        echo $this->s_file;
        exit();
    }

    private function parseFiles($s_extension)
    {
        $s_hash = sha1($_GET['files']);
        
        if ($this->hasCache($s_hash . '.' . $s_extension)) {
            return;
        }
        
        $a_files = explode(",", $_GET['files']);
        $this->combineFiles($a_files);
        
        $this->writeCache($s_hash . '.' . $s_extension);
    }

    private function combineFiles($a_files)
    {
        foreach ($a_files as $s_file) {
            if (strpos($s_file, ';') !== false) {
                $a_parts = explode(';', $s_file);
                $a_subfiles = array();
                $s_dir = $a_parts[0];
                for ($i = 1; $i < count($a_parts); $i ++) {
                    $a_subfiles[] = $s_dir . DS . $a_parts[$i];
                }
                $this->combineFiles($a_subfiles);
                
                continue;
            }
            $s_file = $this->cleanFilename($s_file);
            
            if (! $this->file->exists(NIV . $s_file)) {
                continue;
            }
            
            $s_content = trim($this->file->readFile(NIV . $s_file));
            
            $this->s_file .= $s_content . "\n";
        }
    }

    private function cleanFilename($s_filename)
    {
        while (strpos($s_filename, '../') !== false || strpos($s_filename, './') !== false) {
            $s_filename = str_replace(array(
                './',
                '../'
            ), array(
                '',
                ''
            ), $s_filename);
        }
        
        return $s_filename;
    }

    private function hasCache($s_filename)
    {
        if (($this->config->getSettings()->get('cache/status') == 1) && ($this->file->exists(NIV . 'files/cache/' . $s_filename))) {
            $this->s_file = $this->file->readFile(NIV . 'files/cache/' . $s_filename);
            return true;
        }
        return false;
    }

    private function writeCache($s_filename)
    {
        if ($this->config->getSettings()->get('cache/status') == 1) {
            $this->file->writeFile(NIV . 'files/cache/' . $s_filename, $this->s_file);
        }
    }
}

$combiner = \loader::inject('\youconix\core\Combiner');