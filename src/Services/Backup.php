<?php
namespace youconix\Core\Services;

class Backup extends \youconix\Core\Services\AbstractService
{

    /**
     *
     * @var \LoggerInterface
     */
    protected $logger;

    /**
     *
     * @var \Builder
     */
    protected $builder;

    /**
     *
     * @var \youconix\core\services\FileHandler
     */
    protected $file;

    /**
     *
     * @var \ConfigInterface
     */
    protected $config;

    protected $s_backupDir;

    /**
     *
     * @var \ZipArchive
     */
    protected $obj_zip;

    /**
     *
     * @var \Headers
     */
    protected $headers;

    protected $s_root;

    /**
     * 
     * @param \LoggerInterface $logger
     * @param \Builder $builder
     * @param \youconix\core\services\FileHandler $file
     * @param \ConfigInterface $config
     * @param \Headers $headers
     */
    public function __construct(\LoggerInterface $logger, \Builder $builder, \youconix\core\services\FileHandler $file, \ConfigInterface $config, \Headers $headers)
    {
        $this->logger = $logger;
        $this->builder = $builder;
        $this->file = $file;
        $this->config = $config;
        $this->headers = $headers;
        
        $s_root = $_SERVER['DOCUMENT_ROOT'] . $this->config->getBase();
        $this->s_root = str_replace(DS . DS, DS, $s_root);
        if (substr($this->s_root, - 1) != DS) {
            $this->s_root .= DS;
        }
        $this->s_backupDir = $this->s_root . 'admin' . DS . 'data' . DS . 'backups';
        
        if (! $this->file->exists($this->s_backupDir)) {
            $this->file->newDirectory($this->s_backupDir);
        }
    }

    /**
     * 
     * @param string $s_name
     * @return NULL|string
     */
    protected function openZip($s_name)
    {
        if (! $this->isZipSupported()) {
            $this->logger->critical('Can not create backup. Zip support is missing');
            return null;
        }
        
        $s_filename = $this->s_backupDir . DS . $s_name . '.zip';
        
        $this->obj_zip = new \ZipArchive();
        
        if ($this->obj_zip->open($s_filename, \ZipArchive::CREATE) !== true) {
            $this->logger->critical('Can not create zip archive in directory ' . $this->s_backupDir . '.');
            return null;
        }
        
        $this->obj_zip->setArchiveComment('Backup created by Miniature-happiness on ' . date('d-m-Y H:i:s') . '.');
        
        return $s_filename;
    }

    /**
     * @return string
     */
    public function createPartialBackup()
    {
        $s_filename = $this->openZip('backup_settings_' . date('d-m-Y_H:i'));
        if (is_null($s_filename)) {
            return null;
        }
        
        /* Add database */
        $this->obj_zip->addFromString('database.sql', $this->backupDatabase());
        $s_directory = NIV;
        
        $s_root = $_SERVER['DOCUMENT_ROOT'] . $this->config->getBase();
        $a_skipDirs = array(
            realpath($s_root . DATA_DIR . DS . 'backups'),
            realpath($s_root . DATA_DIR . DS . 'tmp')
        );
        
        $s_dir = realpath($s_root . DATA_DIR);
        $a_files = $this->file->readFilteredDirectoryNames($s_dir, $a_skipDirs);
        
        $this->s_root .= DATA_DIR;
        $s_rootPath = realPath($_SERVER['DOCUMENT_ROOT'] . DS . DATA_DIR) . DS;
        
        $this->addDirectory($a_files, $this->s_root, $s_rootPath);
        
        $this->obj_zip->close();
        
        return basename($s_filename);
    }

    /**
     * @return string
     */
    public function createBackupFull()
    {
        $s_filename = $this->openZip('full_backup_' . date('d-m-Y_H:i'));
        if (is_null($s_filename)) {
            return null;
        }
        
        /* Add database */
        $this->obj_zip->addFromString('database.sql', $this->backupDatabase());
        
        $s_root = $_SERVER['DOCUMENT_ROOT'] . $this->config->getBase();
        $a_skipDirs = [
            realpath($s_root . DATA_DIR . DS . 'backups'),
            realpath($s_root . DATA_DIR . DS . 'tmp'),
            realpath($s_root . 'files' . DS . 'cache'),
            realpath($s_root . '.git')
        ];
        $s_directory = NIV;
        
        $a_files = $this->file->readFilteredDirectoryNames($s_root, $a_skipDirs);
        
        $s_rootPath = $_SERVER['DOCUMENT_ROOT'] . DS;
        $this->addDirectory($a_files, $this->s_root, $s_rootPath);
        
        $this->obj_zip->close();
        
        return basename($s_filename);
    }

    /**
     * 
     * @param array $a_files
     * @param string $s_pre
     * @param string $s_rootPath
     */
    protected function addDirectory(array $a_files, $s_pre, $s_rootPath)
    {
        foreach ($a_files as $key => $file) {
            if (is_array($file)) {
                $this->obj_zip->addEmptyDir(str_replace($this->s_root, '', $s_pre) . $key);
                $this->addDirectory($a_files[$key], $s_pre . $key . DS, $s_rootPath);
                continue;
            }
            
            if (! is_readable($file)) {
                continue;
            }
            
            $this->obj_zip->addFile($file, str_replace($s_rootPath, '', $file));
        }
    }

    protected function backupDatabase()
    {
        $s_sql = '-- Database dump created by Miniature-happiness on ' . date('d-m-Y H:i:s') . ".\n\n";
        
        $s_sql .= $this->builder->dumpDatabase();
        
        return $s_sql;
    }

    protected function isZipSupported()
    {
        return class_exists('ZipArchive');
    }

    /**
     * 
     * @param string $s_file
     * @throws \Http404Exception
     */
    public function download($s_file)
    {
        $s_file = $this->s_backupDir . DS . $s_file;
        if (! $this->file->exists($s_file)) {
            throw new \Http404Exception();
        }
        
        $this->headers->forceDownloadFile($s_file, 'application/zip');
    }

    public function removeBackups()
    {
        $this->file->removeDirectoryContent($this->s_backupDir, 'zip');
    }
}