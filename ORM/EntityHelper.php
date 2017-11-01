<?php

namespace youconix\core\ORM;

class EntityHelper implements \Entities {

  /**
   *
   * @var \Config
   */
  private $config;

  /**
   *
   * @var \youconix\core\ORM\Proxy
   */
  private $proxy;

  /**
   *
   * @var \youconix\core\services\FileHandler
   */
  private $file;
  private $bo_rebuild = true;
  private $s_cacheFile;
  private $s_siteDir;
  private $s_systemDir;
  private $bo_primary = false;
  private $s_type = null;
  private $s_columnName = null;
  private $a_proxySettings;
  private $s_proxyDir;

  /**
   *
   * @var \stdClass
   */
  private $map;

  public function __construct(\Config $config, \youconix\core\services\FileHandler $file, \youconix\core\ORM\Proxy $proxy) {
    $this->config = $config;
    $this->file = $file;
    $this->proxy = $proxy;

    $s_cacheDir = $this->config->getCacheDirectory();
    $this->s_cacheFile = $s_cacheDir . 'entityMap.php';
    $this->s_siteDir = WEB_ROOT . DS . 'includes' . DS . 'entities';
    $this->s_systemDir = WEB_ROOT . DS . 'vendor' . DS . 'youconix' . DS . 'core' . DS . 'entities';
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

  public function buildMap() {
    $this->map = new \stdClass();

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

    foreach (get_object_vars($this->map) as $s_name => $object) {
      $s_proxy = '<?php ' . PHP_EOL . 'namespace files\cache\proxies;' . PHP_EOL . 'class ' . $s_name . 'Proxy extends ';
      if ($this->file->exists(WEBSITE_ROOT . DS . 'includes' . DS . 'entities' . DS . $s_name . '.php')) {
        $s_proxy .= '\includes\entities\\' . $s_name;
      } else {
        $s_proxy .= '\youconix\core\\entities\\' . $s_name;
      }
      $s_proxy .= ' { ' . PHP_EOL;

      foreach ($object->fields as $s_variable => $a_field) {
        if (($a_field['type'] != 'ManyToOne') && ($a_field['type'] != 'OneToOne')) {
          continue;
        }
        $settings = $a_field['proxySettings'];
        $s_setter = 'setProxy' . ucfirst($settings['value']) . '($value)';
        $s_getter = 'getProxy' . ucfirst($settings['value']) . '()';

        $s_proxy .= 'protected $proxy_' . $settings['value'] . ';' . PHP_EOL .
                'public function ' . $s_setter . '{ $this->proxy_' . $settings['value'] . ' = $value; }' . PHP_EOL .
                'public function ' . $s_getter . '{ return $this->proxy_' . $settings['value'] . '; }' . PHP_EOL .
                'public function ' . $a_field['getter'] . '()' . PHP_EOL . '{' . PHP_EOL .
                '	if (is_null($this->' . $s_variable . ')) { ' . PHP_EOL .
                '	  $repository = \Loader::inject(\'\youconix\core\repositories\\' . $settings['target'] . '\');' . PHP_EOL;

        if ($a_field['type'] == 'ManyToOne') {
          $s_proxy .= '    $this->' . $s_variable . ' = $repository->findBy(["' . $settings['field'] . '" => $this->' . $s_getter . ']);' . PHP_EOL;
        } else {
          $s_proxy .= ' $objects = $repository->findBy(["' . $settings['field'] . '" => $this->' . $s_getter . ']);' . PHP_EOL .
                  '  if (count($objects) > 0) {  $a_keys = array_keys($objects); $this->' . $s_variable . ' = $objects[$a_keys[0]]; }' . PHP_EOL;
        }
        $s_proxy .= '  }' . PHP_EOL .
                '  return parent::' . $a_field['getter'] . '();' . PHP_EOL .
                '}' . PHP_EOL;
      }

      $s_proxy .= '}';

      $s_filename = $this->s_proxyDir . DS . $s_name . 'Proxy.php';
      $this->file->writeFile($s_filename, $s_proxy);
    }
  }

  /**
   * 
   * @return \stdClass
   */
  private function createEntity() {
    $entity = new \stdClass();
    $entity->table = null;
    $entity->primary = null;
    $entity->autoincrement = false;
    $entity->fields = [];

    return $entity;
  }

  /**
   * 
   * @param string $s_file
   */
  private function parseEntity($s_file) {
    $s_content = $this->file->readFile($s_file);
    $entity = $this->createEntity();

    // Get table
    $a_matches = null;
    if (preg_match('/@Table\(name="([a-zA-Z_0-9]+)"\)/si', $s_content, $a_matches)) {
      $entity->table = $a_matches[1];
    }

    $i_start = stripos($s_content, 'class');
    $i_length = (stripos($s_content, 'function') - $i_start);
    $s_block = substr($s_content, $i_start, $i_length);

    $a_rules = preg_split("/\r\n|\n|\r/", $s_block);

    // Get class name
    $a_parts = explode(' ', trim($a_rules[0]));
    $s_className = $a_parts[1];

    // Get rules
    $i_amount = count($a_rules);
    for ($i = 1; $i < $i_amount; $i++) {
      $this->parseRule($entity, $a_rules[$i]);
    }

    if (is_null($entity->table) || is_null($entity->primary) || count($entity->fields) == 0) {
      return;
    }

    if (property_exists($this->map, $s_className)) {
      $this->map->$s_className->fields = array_filter(array_merge($this->map->$s_className->fields, $entity->fields));
    } else {
      $this->map->$s_className = $entity;
    }
  }

  /**
   * 
   * @param \stdClass $entity
   * @param string $s_rule
   */
  private function parseRule($entity, $s_rule) {
    $a_matches = null;
    if ((strpos($s_rule, '/**') !== false) || (strpos($s_rule, '*/') !== false) || (trim($s_rule) == '')) {
      return;
    }
    if (strpos($s_rule, '@Id') !== false) {
      $this->bo_primary = true;
    } else if (strpos($s_rule, '@GeneratedValue') !== false) {
      $entity->autoincrement = true;
    } elseif (preg_match('/@(ManyToOne|OneToOne)\(targetEntity="([a-zA-Z0-9\-_]+)"\)/si', $s_rule, $a_matches)) {
      $this->s_type = $a_matches[1];
      $this->a_proxySettings['target'] = $a_matches[2];
    } elseif ($this->isProxy() && preg_match('/@JoinColumn\(name="([`a-zA-Z0-9_]+)",\s* referencedColumnName="([`a-zA-Z0-9_]+)"\)/si', $s_rule, $a_matches)) {
      $this->a_proxySettings['value'] = $a_matches[1];
      $this->a_proxySettings['field'] = $a_matches[2];
    } else if (preg_match('/@Column\(type="([a-zA-Z0-9_]+)"\)/si', $s_rule, $a_matches)) {
      $this->s_type = $a_matches[1];
    } elseif (preg_match('/@Column\(type="([a-zA-Z0-9_]+)",\s*name="([`a-zA-Z0-9_]+)"\)/si', $s_rule, $a_matches)) {
      $this->s_type = $a_matches[1];
      $this->s_columnName = $a_matches[2];
    } elseif (!is_null($this->s_type) && preg_match('/(private|protected|public)\s+\$([a-zA-Z0-9_]+)/si', $s_rule, $a_matches)) {
      $s_name = $a_matches[2];
      $s_call = $this->name2call($s_name);

      $entity->fields[$s_name]['columnName'] = (is_null($this->s_columnName) ? $s_name : $this->s_columnName);
      $entity->fields[$s_name]['type'] = $this->s_type;
      $entity->fields[$s_name]['getter'] = 'get' . $s_call;
      $entity->fields[$s_name]['setter'] = 'set' . $s_call;

      if ($this->bo_primary) {
        $entity->primary = $entity->fields[$s_name]['columnName'];
      }
      if ($this->isProxy()) {
        $entity->fields[$s_name]['proxySettings'] = $this->a_proxySettings;
      }

      $this->a_proxySettings = null;
      $this->bo_primary = false;
      $this->s_type = null;
      $this->s_columnName = null;
    }
  }

  /**
   * 
   * @return boolean
   */
  private function isProxy() {
    if (($this->s_type == 'ManyToOne') || ($this->s_type == 'OneToOne')) {
      return true;
    }
    return false;
  }

  private function name2call($s_name) {
    $s_call = preg_replace_callback('/_(.?)/', function($a_matches) {
      return ucfirst($a_matches[1]);
    }, $s_name);

    return ucfirst($s_call);
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
    if (!property_exists($this->map, $s_entity)) {
      throw new \RuntimeException('Call to unknown entity ' . $s_entity);
    }

    return $this->map->$s_entity;
  }

  /**
   * @return string
   */
  public function getTimezone() {
    return $this->config->getTimezone();
  }

}
