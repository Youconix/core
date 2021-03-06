<?php

namespace youconix\Core\ORM;

use youconix\Core\ORM\AbstractEntity;

abstract class AbstractRepository extends \youconix\Core\AbstractObject
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
   * @var \EntityManager
   */
  protected $manager;

  /**
   *
   * @var array
   */
  protected $a_cache = [];

  /**
   *
   * @var array
   */
  protected $a_validation = [];

  /**
   * 
   * @param \EntityManager $manager
   * @param AbstractEntity $model
   * @param \BuilderInterface $builder
   */
  public function __construct(\EntityManager $manager, AbstractEntity $model,
			      \BuilderInterface $builder)
  {
    $modelName = explode('\\', get_class($model));
    $this->model = \Loader::inject('\files\cache\proxies\\' . end($modelName) . 'Proxy');
    $this->model->__setManager($manager);
    $this->builder = $builder;
    $this->manager = $manager;

    $this->a_map = $manager->getHelper()->get($model->getEntityName());
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
   * @param \stdClass $a_data
   * @return Entity
   */
  protected function getModel(\stdClass $data = null)
  {
    $model = clone $this->model;
    $model->__setManager($this->manager);
    if (!is_null($data)) {
      $model->__setORMData($data);
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

    $primaryField = $this->a_map->getPrimary()['field'];
    $this->builder->select($this->a_map->getTable(), '*')
	->getWhere()
	->bindInt($primaryField, $i_id);

    $database = $this->builder->getResult();
    if ($database->num_rows() == 0) {
      return null;
    }
    $data = $database->fetch_object();

    $model = $this->getModel($data[0]);
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
    $where = $this->builder->select($this->a_map->getTable(), '*')->getWhere();
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
    $this->builder->select($this->a_map->getTable(), '*');
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
    foreach ($database->fetch_object() as $item) {
      $primaryField = $this->a_map->getPrimary()['field'];
      $primary = $item->$primaryField;

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
    $s_field = $this->a_map->primary_field;
    $getter = $this->a_map->fields[$s_field]['getter'];

    return ['field' => $this->a_map->primary, 'field_name' => $s_field, 'value' => $entity->$getter()];
  }

  /**
   *
   * @param Entity $entity
   */
  public function save(Entity $entity)
  {
    $entity->performValidation();

    $a_primary = $this->getPrimary($entity);

    if (is_null($a_primary['value'])) {
      $this->add($a_primary, $entity);

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
   */
  protected function add(array $a_primary, Entity $entity)
  {
    $this->builder->insert($this->a_map->getTable());
    $this->buildSave($entity, $a_primary['field_name']);
    $database = $this->builder->getResult();

    if ($this->a_map->autoincrement) {
      $setter = $this->a_map->fields[$a_primary['field_name']]['setter'];
      $entity->$setter($database->getId());
    }
    
    $this->saveReferences($entity, $a_primary);
  }

  /**
   *
   * @param array $a_primary
   * @param Entity $entity
   */
  protected function update(array $a_primary, Entity $entity)
  {
    $this->builder->update($this->s_table);
    $this->buildSave($entity, $a_primary['field_name']);
    $this->builder->getWhere()->bindInt($a_primary['field'], $a_primary['value']);
    $this->builder->getResult();
    
    $this->saveReferences($entity, $a_primary);
  }

  /**
   * Builds the query
   * 
   * @param Entity $model
   * @param string $s_primaryField
   */
  protected function buildSave(Entity $model, $s_primaryField)
  {
    $data = $model->__getORMData();
    
    foreach ($this->a_map->fields as $s_field => $a_settings) {
      $getter = $a_settings['getter'];

      if ($s_field == $s_primaryField) {
	continue;
      }

      switch ($a_settings['type']) {
	case 'smallint':
	case 'bigint':
	case 'integer':
	  $this->builder->bindInt($a_settings['columnName'], $data->$s_field);
	  break;
	case 'boolean':
	  $i_value = $data->$s_field;
	  if ($i_value === true) {
	    $i_value = 1;
	  } elseif ($i_value === false) {
	    $i_value = 0;
	  }

	  $this->builder->bindString($a_settings['columnName'], $i_value);
	  break;
	case 'float':
	  $this->builder->bindFloat($a_settings['columnName'], $data->$s_field);
	  break;
	case 'blob':
	  $this->builder->bindBlob($a_settings['columnName'], $data->$s_field);
	  break;	
	default:
	  $this->builder->bindString($a_settings['columnName'], $data->$s_field);
	  break;
      }
    }
  }
  
  protected function saveReferences(Entity $model, array $a_primary)
  {
    foreach ($this->a_map->fields as $s_field => $a_settings) {
      switch ($a_settings['type']) {
	case 'ManyToOne':
	  break;
	
	case 'OneToMany':
	  $reference = $this->helper->get($a_settings['proxySettings']['target']);
	  $valueGetter = $reference->fields[$a_settings['proxySettings']['value']]['getter'];
	  $i_value = $model->$getter()->$valueGetter();
	  $s_table = $this->a_map->getTable();
	  $s_referenceTable = $reference->getTable();
	  
	  $this->builder->upsert($s_table.'_'.$s_referenceTable, 'id')
	      ->bindInt($s_table.'_id', $a_primary['value'])
	      ->bindInt($s_referenceTable.'_id', $i_value)
	      ->getResult();
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
