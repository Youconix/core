<?php
namespace core\services;

class Backup extends \core\services\Service {
	/**
	 * 
	 * @var \Logger
	 */
	protected $logger;
	
	/**
	 * 
	 * @var \Builder
	 */
	protected $builder;
	
	/**
	 * 
	 * @var \core\services\FileHandler
	 */
	protected $file;
	
	/**
	 * @var \Config
	 */
	protected $config;
	
	protected $s_backupDir;
	
	/**
	 * @var \ZipArchive 
	 */
	protected $obj_zip;
	
	protected $s_root;
	
	public function __construct(\Logger $logger,\Builder $builder,\core\services\FileHandler $file,\Config $config){
		$this->logger = $logger;
		$this->builder = $builder;
		$this->file = $file;
		$this->config = $config;
		
		$s_root = $_SERVER['DOCUMENT_ROOT'] . $this->config->getBase();
		$this->s_root = str_replace(DS.DS,DS,$s_root);
		if( substr($this->s_root,-1) != DS ){
			$this->s_root .= DS;
		}
		$this->s_backupDir = $this->s_root.'admin'.DS.'data'.DS.'backups';
		
		if( !$this->file->exists($this->s_backupDir) ){
			$this->file->newDirectory($this->s_backupDir);
		}
		
		ini_set('display_errors','on');
		error_reporting(E_ALL);
	}
	
	protected function openZip($s_name){
		if (! $this->isZipSupported ()) {
			$this->logger->critical ( 'Can not create backup. Zip support is missing' );
			return null;
		}
		
		$s_filename = $this->s_backupDir.DS . $s_name . '.zip';
		
		$this->obj_zip = new \ZipArchive ();
		
		if ($this->obj_zip->open ( $s_filename , \ZipArchive::CREATE ) !== true) {
			$this->logger->critical ( 'Can not create zip archive in directory ' . $this->s_backupDir . '.' );
			return null;
		}
		
		$this->obj_zip->setArchiveComment ( 'Backup created by Miniature-happiness on ' . date ( 'd-m-Y H:i:s' ) . '.' );
		
		return $s_filename;
	}
	
	public function createPartialBackup(){
		$s_filename = $this->openZip('backup_' . date ( 'd-m-Y_H:i' ));
		if( is_null($s_filename) ){
			return null;
		}
		
		/* Add database */
		$this->obj_zip->addFromString ( 'database.sql', $this->backupDatabase () );
		$s_directory = NIV;
		
		$s_root = $_SERVER['DOCUMENT_ROOT'] . $this->config->getBase();
		$a_skipDirs = array($s_root.'admin'.DS.'data'.DS.'tmp',$s_root.'admin'.DS.'data'.DS.'backups');
		$a_files = $this->file->readFilteredDirectory($s_root.'admin'.DS.'data',$a_skipDirs);
		
		$this->s_root .= 'admin'.DS.'data'.DS;
		$this->addDirectory ($a_files,$this->s_root );
	
		$this->obj_zip->close ();
		
		return $s_filename;
	}
	
	public function createBackupFull() {
		$s_filename = $this->openZip('full_backup_' . date ( 'd-m-Y_H:i' ));
		if( is_null($s_filename) ){
			return null;
		}
	
		/* Add database */
		$this->obj_zip->addFromString ( 'database.sql', $this->backupDatabase () );
	
		$s_root = $_SERVER['DOCUMENT_ROOT'] . $this->config->getBase();
		$a_skipDirs = array($s_root.DATA_DIR.DS . 'backups',$s_root.DATA_DIR.DS . 'tmp',$s_root.'files',$s_root.'.git');
		$s_directory = NIV;
		
		$a_files = $this->file->readFilteredDirectory($s_root,$a_skipDirs);
		
		$this->addDirectory ($a_files,$this->s_root );
	
		$this->obj_zip->close ();
	
		return $s_filename;
	}
	
	protected function addDirectory($a_files, $s_pre) {
		foreach ( $a_files as $key => $file ) {
			if( !is_object($a_files[$key]) ){				
				$this->obj_zip->addEmptyDir ( str_replace($this->s_root,'',$s_pre).$key );
				$this->addDirectory ($a_files[$key],$s_pre .$key.DS );
				continue;
			}
			
			if( $file->getPathname() == '.' || $file->getPathname() == '..' ){
				continue;
			}
			
			if( !file_exists($file->getPathname()) || !$file->isReadable() ){
				continue;
			}
			$this->obj_zip->addFile ($file->getPathname(),str_replace($this->s_root,'',$file->getPathname()) ) ;
		}
	}
	
	protected function backupDatabase() {
		$s_sql = '-- Database dump created by Miniature-happiness on ' . date ( 'd-m-Y H:i:s' ) . ".\n\n";
	
		$s_sql .= $this->builder->dumpDatabase ();
	
		return $s_sql;
	}
	
	protected function isZipSupported() {
		return class_exists ( 'ZipArchive' );
	}
}