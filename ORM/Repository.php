<?php

namespace youconix\core\ORM;

use youconix\core\ORM\Entity;

abstract class Repository extends \youconix\core\Object
{

  /**
   *
   * @var array
   */
  protected $a_map;

  /**
   *
   * @var Entity
   */
  protected $model;

  /**
   * 
   * @var \Builder
   */
  protected $builder;

  /**
   *
   * @var \Entities
   */
  protected $helper;

  /**
   *
   * @var array
   */
  protected $a_cache = [];

  /**
   *
   * @var string
   */
  protected $s_timezone;

  /**
   *
   * @var array
   */
  protected $a_validation = [];

  /**
   * 
   * @param \Entities $helper
   * @param Entity $model
   * @param \Builder $builder
   */
  public function __construct(\Entities $helper, Entity $model,
			      \Builder $builder)
  {
    $modelName = explode('\\', get_class($model));
    $this->model = \Loader::inject('\files\cache\proxies\\' . end($modelName) . 'Proxy');
    $this->builder = $builder;
    $this->helper = $helper;

    $this->a_map = $helper->get($model->getEntityName());
    $this->s_timezone = $helper->getTimezone();
    $this->clear();
  }

  /**
   * Returns if the object should be treated as singleton
   *
   * @return boolean True if the object is a singleton
   */
  public static function isSingleton()
  {
    return true;
  }

  /**
   *
   * @param array $a_data
   * @return Entity
   */
  protected function getModel(array $a_data = [])
  {
    $model = clone $this->model;

    // Load proxies
    foreach ($this->a_map->fields as $column) {
      switch ($column['type']) {
	case 'ManyToOne':
	case 'OneToOne':

	  $s_dataField = $column['proxySettings']['value'];
	  if (array_key_exists($column['proxySettings']['value'], $this->a_map->fields)) {
	    $s_dataField = $this->a_map->fields[$column['proxySettings']['value']]['columnName'];
	  }

	  if (!array_key_exists($s_dataField, $a_data)) {
	    continue;
	  }

	  $s_setter = 'setProxy' . ucfirst($column['proxySettings']['value']);
	  $model->$s_setter($a_data[$s_dataField]);
	  break;
	default:
	  continue;
      }
    }

    // Load data
    foreach ($this->a_map->fields as $column) {
      $s_columnName = $column['columnName'];
      $s_call = $column['setter'];

      if (!array_key_exists($s_columnName, $a_data)) {
	continue;
      }
      if (!method_exists($model, $s_call)) {
	continue;
      }

      $value = $a_data[$s_columnName];

      switch ($column['type']) {
	case 'date':
	case 'time':
	case 'datetime':
	  if (is_int($value)) {
	    $value = date('Y-m-d H:i:s', $value);
	  }
	  $value = new \DateTime($value);
	  break;
	case 'datetimez':
	  $value = new \DateTime($value, new \DateTimeZone($this->s_timezone));
	  break;
	case 'object':
	case 'array':
	  $value = unserialize($value);
	  break;
	case 'json_array':
	  $value = json_decode($value);
	  break;
	case 'simple_array':
	  $value = explode(',', $value);
	  break;
	case 'boolean':
	  $value = (boolean) $value;
	  break;
	case 'smallint':
	case 'bigint':
	case 'integer':
	  $value = (int) $value;
	  break;
	case 'float':
	  $value = (float) $value;
	  break;
	case 'ManyToOne':
	case 'OneToOne':
	  continue;
      }

      $model->$s_call($value);
    }

    return $model;
  }

  /**
   * Returns the item with the given id
   *
   * @param int $i_id
   *            The id
   * @throws \RuntimeException If the item does not exists
   * @return null|Entity The item
   */
  public function find($i_id)
  {
    if (array_key_exists($i_id, $this->a_cache)) {
      return $this->a_cache[$i_id];
    }

    $this->builder->select($this->a_map->table, '*')
	->getWhere()
	->bindInt($this->a_map->primary, $i_id);

    $database = $this->builder->getResult();
    if ($database->num_rows() == 0) {
      return null;
    }
    $a_data = $database->fetch_assoc();

    $model = $this->getModel($a_data[0]);
    $this->a_cache[$i_id] = $model;
    return $model;
  }

  /**
   * Finds the items with the given keys and values
   *
   * @param array $a_relations
   *            The key and values as associate array
   * @param array $a_options
   * @return Entity[]
   */
  public function findBy(array $a_relations, array $a_options = [])
  {
    $where = $this->builder->select($this->a_map->table, '*')->getWhere();
    foreach ($a_relations as $s_key => $s_value) {
      $where->bindString($s_key, $s_value);
    }

    if (count($a_options) > 0) {
      if (!array_key_exists('orderBy', $a_options)) {
	$a_options['orderBy'] = 'ASC';
      }
      if (array_key_exists('order', $a_options)) {
	$this->builder->order($a_options['order'], $a_options['orderBy']);
      }
      if (!array_key_exists('offset', $a_options)) {
	$a_options['offset'] = 0;
      }
      if (array_key_exists('limit', $a_options)) {
	$this->builder->limit($a_options['limit'], $a_options['offset']);
      }
    }
    $database = $this->builder->getResult();
    return $this->databaseResult2objects($database);
  }

  public function getAll($i_start = -1, $i_limit = 25)
  {
    $this->builder->select($this->a_map->table, '*');
    if ($i_start != -1) {
      $this->builder->limit($i_limit, $i_start);
    }
    $database = $this->builder->getResult();
    return $this->databaseResult2objects($database);
  }

  /**
   * 
   * @param resource $database
   * @return array
   */
  protected function databaseResult2objects($database)
  {
    $a_result = [];
    if ($database->num_rows() == 0) {
      return [];
    }
    foreach ($database->fetch_assoc() as $item) {
      $primary = $item[$this->a_map->primary];

      if (array_key_exists($primary, $this->a_cache)) {
	$model = $this->a_cache[$primary];
      } else {
	$model = $this->getModel($item);
	$this->a_cache[$primary] = $model;
      }

      $a_result[$primary] = $model;
    }
    return $a_result;
  }

  /**
   * 
   * @param Entity $entity
   * @return array
   */
  private function getPrimary(Entity $entity)
  {
    $s_field = $this->a_map->primary;
    $getter = $this->a_map[$s_field]['getter'];

    return ['field' => $s_field, 'value' => $entity->$getter()];
  }

  /**
   *
   * @param Entity $entity
   */
  public function save(Entity $entity)
  {
    $this->performValidation();

    $a_primary = $this->getPrimary($entity);

    if (is_null($a_primary['value'])) {
      $this->add($entity);

      $a_primary = $this->getPrimary($entity);
      $this->a_cache[$a_primary['value']] = $entity;
    } else {
      $this->update($a_primary, $entity);
    }

    return $this;
  }

  /**
   *
   * @param array $a_primary
   * @param Entity $entity
   *            Adds the item to the database
   */
  protected function add(array $a_primary, Entity $entity)
  {
    $this->builder->insert($this->a_map->table);
    $this->buildSave($entity);
    $database = $this->builder->getResult();

    if ($this->a_map->autoincrement) {
      $setter = $this->a_map[$a_primary['field']]['setter'];
      $entity->$setter($database->getId());
    }
  }

  /**
   *
   * @param array $a_primary
   * @param Entity $model
   *            Updates the item in the database
   */
  protected function update(array $a_primary, Entity $model)
  {
    $this->builder->update($this->s_table);
    $this->buildSave($model);
    $this->builder->getWhere()->bindInt($a_primary['field'], $a_primary['value']);
    $this->builder->getResult();
  }

  /**
   * Builds the query
   */
  protected function buildSave(Entity $model)
  {
    foreach ($this->a_map->fields as $s_field => $a_settings) {
      $getter = $a_settings['getter'];

      switch ($a_settings['type']) {
	case 'smallint':
	case 'bigint':
	case 'integer':
	case 'boolean':
	  $this->builder->bindInt($s_field, $model->$getter());
	  break;
	case 'float':
	  $this->builder->bindFloat($s_field, $model->$getter());
	  break;
	case 'blob':
	  $this->builder->bindBlob($s_field, $model->$getter());
	  break;
	case 'date':
	case 'time':
	case 'datetime':
	case 'datetimez':
	  $this->builder->bindString($s_field, $model->$getter()->getTimestamp());
	  break;
	case 'object':
	case 'array':
	  $this->builder->bindString($s_field, serialize($model->$getter()));
	  break;
	case 'json_array':
	  $this->builder->bindString($s_field, json_encode($model->$getter()));
	  break;
	case 'simple_array':
	  $this->builder->bindString($s_field, implode(',', $model->getter()));
	  break;
	case 'ManyToOne':
	case 'OneToOne':
	  break;
	default:
	  $this->builder->bindString($s_field, $model->getter());
	  break;
      }
    }
  }

  /**
   * Deletes the item from the database
   */
  public function delete(Entity $entity)
  {
    $s_primaryField = $this->a_map->primary;
    $s_getter = $this->a_map->fields[$s_primaryField]->getter();
    $i_primaryValue = $entity->$s_getter();

    $this->builder->delete($this->s_table);
    $this->builder->getWhere()->bindInt($s_primaryField, $i_primaryValue);
    $this->builder->getResult();

    return $this;
  }

  public function clear()
  {
    $this->a_cache = [];
  }
}
