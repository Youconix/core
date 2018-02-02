<?php

namespace youconix\core\models;

class ControlPanelModules
{
  /**
   *
   * @var \Builder
   */
  private $builder;
  
  /**
   *
   * @var \youconix\core\services\File
   */
  private $file;

  /**
   *
   * @var \youconix\core\services\Xml
   */
  private $xml;

  /**
   * PHP5 constructor
   *
   * @param \Builder $builder
   * @param \youconix\core\services\File $file
   * @param \youconix\core\services\XML $xml
   */
  public function __construct(\Builder $builder,
                              \youconix\core\services\File $file,
                              \youconix\core\services\Xml $xml)
  {
    $this->builder = $builder;
    $this->file = $file;
    $this->xml = $xml;
  }

  /**
   * Returns the admin modules directory
   *
   * @return string The directory
   */
  public function getDirectory()
  {
    return $_SERVER['DOCUMENT_ROOT'].DS.'admin'.DS.'modules';
  }

  /**
   * Returns the module names
   *
   * @return array The names
   */
  protected function getModules()
  {
    $s_dir = $this->getDirectory();
    $a_directory = $this->file->readDirectory($s_dir, false, true);

    $a_files = array();
    foreach ($a_directory as $s_module) {
      if (!is_dir($s_dir.DIRECTORY_SEPARATOR.$s_module) || !$this->file->exists($s_dir.DIRECTORY_SEPARATOR.$s_module.'/settings.xml')) {
        continue;
      }

      $a_files[] = $s_module;
    }

    return $a_files;
  }

  /**
   * Returns the names of the installed modules
   *
   * @return array The names
   */
  public function getInstalledModulesList()
  {
    $a_filesRaw = $this->getModules();
    $a_files = array();

    $this->builder->select('admin_modules', 'name');
    $database = $this->builder->getResult();

    if ($database->num_rows() > 0) {
      $data = $database->fetch_assoc();

      foreach ($data as $a_name) {
        if (in_array($a_name['name'], $a_filesRaw)) {
          $a_files[] = $a_name['name'];
        }
      }
    }

    return $a_files;
  }

  /**
   * Returns the installed modules
   *
   * @return array The modules
   */
  public function getInstalledModules()
  {
    $a_modules = array(
        'installed' => array(),
        'upgrades' => array()
    );

    $this->builder->select('admin_modules', '*');
    $database = $this->builder->getResult();

    if ($database->num_rows() > 0) {
      $a_data = $database->fetch_assoc();
      $s_dir = $this->getDirectory();

      foreach ($a_data as $a_item) {
        $obj_settings = $this->xml->cloneService();
        $obj_settings->load($s_dir.DIRECTORY_SEPARATOR.$a_item['name'].DIRECTORY_SEPARATOR.'settings.xml');
        if ($obj_settings->get('module/version') > $a_item['version']) {
          $a_item['versionNew'] = $obj_settings->get('module/version');
          $a_modules['upgrades'][] = $a_item;
        } else {
          $a_modules['installed'][] = $a_item;
        }
      }
    }

    return $a_modules;
  }

  /**
   * Returns the new (not installed) modules
   *
   * @return array The modules
   */
  public function getNewModules()
  {
    $a_filesRaw = $this->getModules();
    $a_files = array();

    $this->builder->select('admin_modules', 'name');
    $database = $this->builder->getResult();

    if ($database->num_rows() > 0) {
      $a_data = $database->fetch_row();
      $a_modules = array();
      foreach ($a_data as $a_item) {
        $a_modules[] = $a_item[0];
      }

      foreach ($a_filesRaw as $s_file) {
        if (!in_array($s_file, $a_modules)) {
          $a_files[] = $s_file;
        }
      }
    }

    foreach ($a_files as $key => $s_file) {
      /* Get module data */
      $a_files[$key] = $this->getModuleData($s_file);
    }

    return $a_files;
  }

  /**
   * Returns the module data from the settings-file
   *
   * @param string $s_module
   *            The module name
   * @return array The name, author, version and description
   */
  public function getModuleData($s_module, $bo_full = false)
  {
    $a_data = array(
        'name' => '',
        'author' => '',
        'version' => '',
        'description' => ''
    );

    $s_dir = $this->getDirectory();

    $obj_settings = $this->xml->cloneService();
    $obj_settings->load($s_dir.DIRECTORY_SEPARATOR.$s_module.DIRECTORY_SEPARATOR.'settings.xml');

    foreach ($a_data as $s_key => $value) {
      if ($obj_settings->exists('module/'.$s_key)) {
        $a_data[$s_key] = $obj_settings->get('module/'.$s_key);
      }
    }

    if ($bo_full) {
      $a_keys = array(
          'install',
          'upgrade',
          'deinstall'
      );
      foreach ($a_keys as $s_key) {
        $a_data[$s_key] = $obj_settings->get('module/'.$s_key);
      }
    }

    return $a_data;
  }

  /**
   * Installs the given module
   *
   * @param string $s_name
   * @throws \Exception If the module throws an exception
   */
  public function installModule($s_name)
  {
    /* Check if module not exists */
    $this->builder->select('admin_modules', 'id')
        ->getWhere()
        ->bindString('name', $s_name);
    $database = $this->builder->getResult();
    if ($database->num_rows() != 0) {
      return;
    }

    $s_dir = $this->getDirectory();
    if (!$this->file->exists($s_dir.DIRECTORY_SEPARATOR.$s_name.DIRECTORY_SEPARATOR.'settings.xml')) {
      return;
    }

    $a_data = $this->getModuleData($s_name, true);

    $this->builder->insert('admin_modules')
        ->bindString('name', $a_data['name'])
        ->bindInt('installed', time())
        ->bindString('author', $a_data['author'])
        ->bindString('description', $a_data['description'])
        ->bindString('version', $a_data['version']);

    $this->builder->getResult();

    if (!empty($a_data['install'])) {
      /* Run module installer */
      require ($s_dir.DIRECTORY_SEPARATOR.$s_name.DIRECTORY_SEPARATOR.$a_data['install']);
    }
  }

  /**
   * Removes the given module
   *
   * @param int $i_id
   * @throws \Exception If the module throws an exception
   */
  public function removeModule($i_id)
  {
    /* Check if module exists */
    $this->builder->select('admin_modules', 'name')
        ->getWhere()
        ->bindInt('id', $i_id);
    $database = $this->builder->getResult();
    if ($database->num_rows() == 0) {
      return;
    }

    $s_name = $database->result(0, 'name');

    if (in_array($s_name, $this->getCoreModules())) {
      /*
       * Framework modules
       * Do not remove
       */
    }

    $s_dir = $this->getDirectory();

    $this->builder->delete('admin_modules')
        ->getWhere()
        ->bindInt('id', $i_id);
    $this->builder->getResult();

    if ($this->file->exists($s_dir.DIRECTORY_SEPARATOR.$s_name.DIRECTORY_SEPARATOR.'settings.xml')) {
      $a_data = $this->getModuleData($s_name, true);

      if (!empty($a_data['deinstall'])) {
        /* Run module deinstaller */
        require ($s_dir.DIRECTORY_SEPARATOR.$s_name.DIRECTORY_SEPARATOR.$a_data['deinstall']);
      }
    }

    $this->file->deleteDirectory($s_dir.DIRECTORY_SEPARATOR.$s_name);
  }

  /**
   * Upgrades the given module
   *
   * @param int $i_id
   *            The module ID
   * @throws \Exception If the module throws an exception
   */
  public function updateModule($i_id)
  {
    /* Check if module exists */
    $this->builder->select('admin_modules', 'name')
        ->getWhere()
        ->bindInt('id', $i_id);
    $database = $this->builder->getResult();
    if ($database->num_rows() == 0) {
      return;
    }

    $s_name = $database->result(0, 'name');
    $s_dir = $this->getDirectory();

    if (!$this->file->exists($s_dir.DIRECTORY_SEPARATOR.$s_name.DIRECTORY_SEPARATOR.'settings.xml')) {
      return;
    }

    $this->builder->update('admin_modules', 'version', 's', $a_data['version'])
        ->getWhere()
        ->bindInt('id', $i_id);
    $this->builder->getResult();

    $a_data = $this->getModuleData($s_name, true);

    if (!empty($a_data['upgrade'])) {
      /* Run module upgrader */
      require ($s_dir.DIRECTORY_SEPARATOR.$s_name.DIRECTORY_SEPARATOR.$a_data['upgrade']);
    }
  }

  /**
   * 
   * @return array
   */
  public function getCoreModules()
  {
    return ['general', 'settings', 'statistics'];
  }

  /**
   * 
   * @param string $s_extension
   * @return string
   */
  public function getCacheFile($s_extension)
  {
    return NIV.'files'.DS.'cache'.DS.'admin_modules.'.$s_extension;
  }
}