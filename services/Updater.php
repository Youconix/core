<?php
namespace youconix\core\services;

class Updater extends \youconix\core\services\Service
{

    /**
     *
     * @var \settings
     */
    protected $settings;

    /**
     *
     * @var \youconix\core\services\CurlManager
     */
    protected $curlManager;

    /**
     *
     * @var \youconix\core\services\FileHandler
     */
    protected $file;

    /**
     *
     * @var \youconix\core\services\Xml
     */
    protected $xml;

    protected $s_remote;

    protected $s_version;

    public function __construct(\Settings $settings, \youconix\core\services\CurlManager $curlManager, \youconix\core\services\FileHandler $file, \youconix\core\services\Xml $xml)
    {
        $this->settings = $settings;
        $this->curlManager = $curlManager;
        $this->file = $file;
        $this->xml = $xml;
        
        $this->s_remote = $settings::REMOTE;
        $this->s_version = $settings->get('version');
    }

    public function checkUpdates()
    {
         $this->curlManager->performGetCall($this->s_remote . 'checkupdates/' . $this->s_version, array());
        $i_header = $this->curlManager->getHeaderCode();
        
        if ($i_header != 200) {
            return null;
        }
        
        $s_content = $this->curlManager->getContent();
        $this->xml->loadXML($s_content);
        $this->xml->save(NIV . 'files/updater.xml');
        
        return $this->xml;
    }
}