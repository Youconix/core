<?php

namespace youconix\Core\ORM;

class EntityHelper implements \Entities {
  /**
   *
   * @var \ConfigInterface
   */
  private $config;

  /**
   *
   * @var \youconix\Core\ORM\Proxy
   */
  private $proxy;

  /**
   *
   * @var \youconix\Core\Services\FileHandler
   */
  private $file;
  
  /**
   *
   * @var \stdClass
   */
  private $map;
  
  private $bo_rebuild = true;
  private $s_cacheFile;
  private $s_siteDir;
  private $s_systemDir;
  private $s_proxyDir;

  public function __construct(\ConfigInterface $config, \youconix\Core\Services\FileHandler $file, \youconix\Core\ORM\Proxy $proxy) {
    $this->config = $config;
    $this->file = $file;
    $this->proxy = $proxy;
    
    $s_cacheDir = $this->config->getCacheDirectory();
    $this->s_cacheFile = $s_cacheDir . 'entityMap.php';
    $this->s_siteDir = WEB_ROOT . DS . 'Includes' . DS . 'entities';
    $this->s_systemDir = WEB_ROOT . DS . 'vendor' . DS . 'youconix' . DS . 'Core' . DS . 'src'.DS.'Entities';
    $this->s_proxyDir = $s_cacheDir . 'proxies';
    
    if (!$file->exists($this->s_proxyDir)) {
      $file->newDirectory($this->s_proxyDir);
    }
  }

  /**
   * Returns if the object should be treated as singleton
   *
   * @return boolean True if the object is a singleton
   */
  public static function isSingleton() {
    return true;
  }
  
  public function dropCache()
  {
    $this->file->deleteDirectoryContent($this->s_proxyDir);
    if ($this->file->exists($this->s_cacheFile)) {
      $this->file->deleteFile($this->s_cacheFile);
    }
  }

  public function buildMap() {
    $this->map = new \stdClass();
    $this->map->repositories = [];
    $this->map->entities = [];

    if ($this->file->exists($this->s_cacheFile) && !defined('DEBUG')) {
      $this->bo_rebuild = false;
      $this->readMap();
      return;
    }

    $a_siteEntitites = $this->file->readFilteredDirectoryNames($this->s_siteDir, [], 'php');
    foreach ($a_siteEntitites as $s_file) {
      $this->parseEntity($s_file);
    }

    $a_systemEntities = $this->file->readFilteredDirectoryNames($this->s_systemDir, [], 'php');
    foreach ($a_systemEntities as $s_file) {
      $this->parseEntity($s_file);
    }
    
    if (!defined('DEBUG')) {
      $this->file->writeFile($this->s_cacheFile, serialize($this->map));
    }
  }

  public function buildProxies() {
    if (!$this->bo_rebuild) {
      return;
    }

    foreach ($this->map->entities as $s_name => $object) {
      $s_proxy = '<?php
      namespace files\cache\proxies;
      
      class ' . $s_name . 'Proxy extends ';
      if ($this->file->exists(WEBSITE_ROOT . DS . 'Includes' . DS . 'Entities' . DS . $s_name . '.php')) {
        $s_proxy .= '\Includes\Entities\\' . $s_name;
      } else {
        $s_proxy .= '\youconix\Core\\Entities\\' . $s_name;
      }
      $s_proxy .= ' {
      protected $manager;
      public function __setManager(\EntityManager $manager)
      {
	$this->manager = $manager;
      }
      public function __setORMData(\stdClass $data)
      {'.
      $this->createProxySetter($object);
      
      $s_proxy .= '}
      public function __getORMData()
      {'.
	  $this->createProxyGetter($object).'
      }';
      foreach ($object->getReferences() as $s_variable => $a_field) {	
        $s_proxy .= "\t".'protected $proxy_' . $a_field['columnName'] . ';' . PHP_EOL .
                "\t".'public function ' . $a_field['getter'] . '()' . PHP_EOL . "\t{" . PHP_EOL .
                "\t\t".'if (is_null($this->' . $s_variable . ')) { ' . PHP_EOL .
                "\t\t\t".'$repository = $this->manager->getRepository(\'' . $a_field['target'] . '\');' . PHP_EOL;

        if ($a_field['type'] == 'ManyToOne') {
          $s_proxy .= "\t\t\t".'$this->' . $s_variable . ' = $repository->findBy(["' . $a_field['field'] . '" => $this->proxy_' . $a_field['columnName'] . ']);' . PHP_EOL;
        } else {
          $s_proxy .= "\t\t\t".'$objects = $repository->findBy(["' . $a_field['field'] . '" => $this->proxy_' . $a_field['columnName'].']);' . PHP_EOL .
                  "\t\t\t".'if (count($objects) > 0) {  $a_keys = array_keys($objects); $this->' . $s_variable . ' = $objects[$a_keys[0]]; }' . PHP_EOL;
        }
        $s_proxy .= "\t\t}" . PHP_EOL .
                "\t\t".'return parent::' . $a_field['getter'] . '();' . PHP_EOL .
                "\t}" . PHP_EOL;
      }

      $s_proxy .= '}';

      $s_filename = $this->s_proxyDir . DS . $s_name . 'Proxy.php';
      $this->file->writeFile($s_filename, $s_proxy);
    }
  }
  
  private function createProxySetter($object)
  {
    $setter = '';
    $getters = [];
    
    foreach ($object->getFields() as $s_variable => $a_field) {
      $value = '$data->'.$a_field['columnName'];      
      
      switch ($a_field['type']) {
	case 'date':
	case 'time':
	case 'datetime':
	  $value = "
	  ( (is_int(".$value.")) ? new \DateTime(date('Y-m-d H:i:s', ".$value.")): new \DateTime(".$value.") )";
	  break;
	case 'datetimez':
	  $value = "new \DateTime(".$value.", new \DateTimeZone(".$this->getTimezone()."))";
	  break;
	case 'object':
	case 'array':
	  $value = "unserialize(".$value.")";
	  break;
	case 'json_array':
	  $value = "json_decode(".$value.")";
	  break;
	case 'simple_array':
	  $value = "explode(',', ".$value.")";
	  break;
	case 'boolean':
	  $value = "(boolean) ".$value;
	  break;
	case 'smallint':
	case 'bigint':
	case 'integer':
	  $value = "(int) ".$value;
	  break;
	case 'float':
	  $value = "(float) ".$value;
	  break;
      }

      $setter .= "\t\t".'$this->'.$a_field['setter'].'('.$value.');
	';
      $getters[$s_variable] = $value;
    }
    $setter .= PHP_EOL;
    
    foreach ($object->getReferences() as $s_variable => $a_field) {
	if (array_key_exists($a_field['value'], $getters)) {
	  $value = $getters[$a_field['value']];
	}
	else {
	  $value = '$data->'.$a_field['value'];
	}
      
	$setter .= '$this->proxy_'.$a_field['columnName'].' = '.$value.';
	';
    }
      
    return $setter.PHP_EOL;
  }
  
  private function createProxyGetter($object)
  {
    $getter = '$data = new \stdClass();
    ';
    
    foreach ($object->getFields() as $s_variable => $a_field) {
      $value = '$this->'.$a_field['getter'];
      
      switch ($a_field['type']) {
	case 'date':
	case 'time':
	case 'datetime':
	case 'datetimez':
	  $value .= '->getTimestamp()';
	  break;
	case 'object':
	case 'array':
	  $value = "serialize(".$value.")";
	  break;
	case 'json_array':
	  $value = "json_encode(".$value.")";
	  break;
	case 'simple_array':
	  $value = "implode(',', ".$value.")";
	  break;
      }

      $getter .= "\t\t".'$data->'.$s_variable.' = '.$value.';
	';
    }
    
    foreach ($object->getReferences() as $s_variable => $a_field) {
      if ($a_field['type'] !== 'OneToOne') {
	continue;
      }
      
      $reference = $this->get($a_field['target']);
      $referenceGetter = $reference->getFields()[$a_field['field']]['getter'];
      
      $getter .= "\t\t".'$data->'.$a_field['columnName'].' = $this->'.$a_field['getter'].'()->'.$referenceGetter.'();
	';
    }
      
    return $getter.'
	return $data;
      '.PHP_EOL;
  }
  
  /**
   * 
   * @param string $s_file
   */
  private function parseEntity($s_file) {
    $s_content = $this->file->readFile($s_file);
   
    $a_matches = null;
    
    // Get namespace
    preg_match('/namespace[\s]+([a-z0-9\-_\\\]+);/si', $s_content, $a_matches);
    $s_namespace = $a_matches[1];
    
    // Get class name
    preg_match('/class\s([a-zA-Z0-9_]+)\s/s', $s_content, $a_matches);
    $s_className = $a_matches[1];
    $s_entity = '\\'.$s_namespace.'\\'.$s_className;
    
    if (array_key_exists($s_className, $this->map->entities)) {
      $entity = $this->map->entities[$s_className];
    }
    else {
      $entity = new EntityTemplate($s_className, $s_entity);
      $this->map->entities[$s_className] = $entity;
    }
    
    $entity->parseEntity($this->file, $s_content);
    
    $this->map->repositories[$s_className] = [
	'entity' => $s_entity,
	'repository' => '\\'.$entity->getRepository()
    ];
  }

  private function readMap() {
    $s_content = $this->file->readFile($this->s_cacheFile);
    $this->map = unserialize($s_content);
  }

  /**
   * Returns the entity layout
   *
   * @param string $s_entity
   * @return array
   */
  public function get($s_entity) {
    if (!array_key_exists($s_entity, $this->map->entities)) {
      throw new \RuntimeException('Call to unknown entity ' . $s_entity);
    }

    return $this->map->entities[$s_entity];
  }
  
  /**
   * 
   * @param string $s_repository
   * @return array
   * @throws \RuntimeException
   */
  public function getRepository($s_repository)
  {
    if (!array_key_exists($s_repository, $this->map->repositories)) {
      throw new \RuntimeException('Call to unknow repository '.$s_repository.'.');
    }
    
    return $this->map->repositories[$s_repository];
  }

  /**
   * @return string
   */
  public function getTimezone() {
    return $this->config->getTimezone();
  }
}

final class EntityTemplate {
  private $entity;
  
  /** 
   *
   * @var string
   */
  private $name;
  
  /**
   *
   * @var string
   */
  private $repository;
  
  /**
   *
   * @var string
   */
  private $table;
  
  /**
   *
   * @var array
   */
  private $primary;
  
  /**
   *
   * @var boolean
   */
  private $autoincrement;
  
  /**
   *
   * @var array
   */
  private $fields = [];
  
  /**
   *
   * @var array
   */
  private $references = [];
  
  /**
   *
   * @var string
   */
  private $content;
  
  public function __construct($name, $entity)
  {
    $this->name = $name;
    $this->entity = $entity;
  }
  
  public function parseEntity(\youconix\core\services\FileHandler $file, $content)
  {
    $this->content = $content;
    
    $this->findTable();
    $this->findRepository($file);
    
    $rules = preg_split("/\r\n|\n|\r/", $content);
    $buffer = $this->createBuffer();
    
    foreach($rules as $rule){
      if (strpos($rule, '* @') === false && strpos($rule, 'public') === false && 
	  strpos($rule, 'protected') === false && strpos($rule, 'private') === false) {
	continue;
      }
      
      $a_matches = null;
    
      if (strpos($rule, '@Id') !== false) {
	$buffer['primary'] = true;
	continue;
      } 
      if (strpos($rule, '@GeneratedValue') !== false) {
	$buffer['autoincrement'] = true;
	continue;
      }
      if (preg_match('/@(ManyToOne|OneToOne)\(targetEntity="([a-zA-Z0-9\-_]+)"\)/si', $rule, $a_matches)) {
	$buffer['type'] = $a_matches[1];
	$buffer['isReference'] = true;
	$buffer['target'] = $a_matches[2];
	continue;
      }
      if (preg_match('/@JoinColumn\(name="([`a-zA-Z0-9_]+)",\s* referencedColumnName="([`a-zA-Z0-9_]+)"\)/si', $rule, $a_matches)) {
	$buffer['isReference'] = true;
	$buffer['value'] = $a_matches[1];
	$buffer['field'] = $a_matches[2];
	continue;
      }
      if (preg_match('/@Column\(type="([a-zA-Z0-9_]+)"\)/si', $rule, $a_matches)) {
	$buffer['type'] = $a_matches[1];
	continue;
      }
      if (preg_match('/@Column\(type="([a-zA-Z0-9_]+)",\s*name="([`a-zA-Z0-9_]+)"\)/si', $rule, $a_matches)) {
	$buffer['type'] = $a_matches[1];
	$buffer['columnName'] = $a_matches[2];
	continue;
      }
      if (!empty($buffer['type']) && preg_match('/(private|protected|public)\s+\$([a-zA-Z0-9_]+)/si', $rule, $a_matches)) {
	$name = $a_matches[2];
	$call = $this->name2call($name);
      
	$getter = 'get' . $call;
	if (strpos($content, 'function '.$getter.'(') === false) {
	  if (strpos($content, 'function is'.$call.'(') === false) {
	    $buffer = $this->createBuffer();
	    continue; // No getter found
	  }
	  if (strpos($content, 'function set'.$call.'(') === false) {
	    continue; // No setter found
	  }
	  $getter = 'is'.$call;
	}

	$buffer['columnName'] = (empty($buffer['columnName']) ? $name : $buffer['columnName']);
	$buffer['getter'] = $getter;
	$buffer['setter'] = 'set' . $call;

	if ($buffer['primary']) {
	  $this->primary = [
	    'field' => $buffer['columnName'],
	    'columnName' => $name,
	    'getter' => $buffer['getter']
	  ];
	}
	
	$isReference = $buffer['isReference'];
	
	unset($buffer['primary']);
	unset($buffer['autoincrement']);
	unset($buffer['isReference']);
	
	if ($isReference) {
	  $this->references[$name] = $buffer;
	}
	else {
	  unset($buffer['cascade']);
	  $this->fields[$name] = $buffer;
	}
	
	$buffer = $this->createBuffer();
      }
    }
    
    $this->content = '';
  }
  
  /**
   * 
   * @return array
   */
  private function createBuffer()
  {
    return [
	'primary' => false,
	'autoincrement' => false,
	'type' => '',
	'columnName' => '',
	'getter' => '',
	'setter' => '',
	'isReference' => false,
	'cascade' => ['detach']
    ];
  }
  
  private function findTable()
  {
    // Get table
    $a_matches = null;
    if (preg_match('/@Table\(name="([a-zA-Z_0-9]+)"\)/si', $this->content, $a_matches)) {
      $this->table = $a_matches[1];
    }
  }
  
  private function findRepository(\youconix\core\services\FileHandler $file)
  {
    $a_matches = null;
    
    if (preg_match('/@ORM\\\Entity\(repositoryClass="([a-zA-Z0-9\\\-_]+)"\)/si', $this->content, $a_matches)) {
      $this->repository = $a_matches[1];
    }
    else {
      $proxy = '<?php
	
      namespace files\cache\proxies;
      
      class '.$this->getName().'Repository extends \youconix\core\ORM\Repository {
	public function __construct(\EntityManager $manager, '.$this->entity.' $model,\Builder $builder)
	{
	  parent::__construct($manager, $model, $builder);
	}
      }';
      
      $file->writeFile(NIV.'files'.DS.'cache'.DS.'proxies'.DS.$this->getName().'Repository.php', $proxy);
      
      $this->repository = 'files\cache\proxies\\'.$this->getName().'Repository';
    }
  }
  
  /**
   * 
   * @param string $name
   * @return string
   */
  private function name2call($name)
  {
    $call = preg_replace_callback('/_(.?)/', function($a_matches) {
      return ucfirst($a_matches[1]);
    }, $name);

    return ucfirst($call);
  }
  
  public function getName()
  {
    return $this->name;
  }
  
  public function getRepository()
  {
    return $this->repository;
  }
  
  public function getTable()
  {
    return $this->table;
  }
  
  public function getPrimary()
  {
    return $this->primary;
  }
  
  public function isAutoincrement()
  {
    return $this->autoincrement;
  }
  
  public function getFields()
  {
    return $this->fields;
  }
  
  public function getReferences()
  {
    return $this->references;
  }
}