<?php
namespace core\services;

class Updater extends \core\services\Service {
	/**
	 * 
	 * @var \settings
	 */
	protected $settings;
	/**
	 * 
	 * @var \core\services\CurlManager
	 */
	protected $curlManager;
	
	/**
	 * @var \core\services\FileHandler
	 */
	protected $file;
	
	/**
	 * @var \core\services\Xml
	 */
	protected $xml;
	
	protected $s_remote;
	protected $s_version;
	
	public function __construct(\Settings $settings,\core\services\CurlManager $curlManager,\core\services\FileHandler $file,\core\services\Xml $xml){
		$this->settings = $settings;
		$this->curlManager = $curlManager;
		$this->file = $file;
		$this->xml = $xml;
		
		$this->s_remote = $settings::REMOTE;
		$this->s_version = $settings->get('version');
	}
	
	public function checkUpdates(){
		$s_content = $this->curlManager->performGetCall($this->s_remote.'checkupdates/'.$this->s_version,array());
		$i_header = $this->curlManager->getHeader();
		
		if( $i_header != 200 ){
			return null;
		}
		
		$s_content = substr($s_content, $this->curlManager->getHeaderSize());
		$this->xml->loadXML($s_content);
		$this->xml->save(NIV.'files/updater.xml');
		
		return $this->xml;
	}
}