<?php

namespace youconix\core\ORM;

use youconix\core\services\Validation;

abstract class Entity
{
  protected $s_table = '';
  
  /**
   *
   * @var Validation
   */
  protected $validation;

  /**
   * @var \Builder $builder
   */
  protected $builder;

  /**
   *
   * @var array
   */
  protected $a_validation = [];

  /**
   *
   * @var bool
   */
  protected $bo_throwError = true;
  
  /**
   *
   * @var array
   */
  protected $a_validationErrors;

  /**
   * 
   * @param \Builder $builder
   * @param Validation $validation
   */
  public function __construct(\Builder $builder, Validation $validation)
  {
    $this->builder = $builder;
    $this->validation = $validation;
    
    $this->detectTableName();
  }
  
  /**
   * 
   * @return string
   */
  public function getEntityName()
  {
    $a_name = explode('\\', get_class($this));
    return end($a_name);
  }
  
  protected function detectTableName()
  {
    if (!empty($this->s_table)) {
      return;
    }
    $s_className = get_class($this);
    $this->s_table = strtolower(preg_replace('/[A-Z]/', '_$0', $s_className));
  }
  
  /**
   * 
   * @return $s_table
   */
  public function getTableName()
  {
    return $this->s_table;
  }

  /**
   * Validates the model
   *
   * @return bool if the model is valid, otherwise false
   */
  public function validate()
  {
    try {
      $this->performValidation();
      return true;
    } catch (\ValidationException $e) {
      return false;
    }
  }

  /**
   * Performs the model validation
   *
   * @throws \ValidationException the model is invalid
   */
  public function performValidation()
  {
    $this->a_validationErrors = [];

    $a_keys = array_keys($this->a_validation);
    foreach ($a_keys as $s_key) {
      if (!isset($this->$s_key)) {
	$this->a_validationErrors[] = 'Error validating non existing field ' . $s_key . '.';
	continue;
      }

      $this->validation->validateField($s_key, $this->$s_key, $this->a_validation[$s_key]);
    }

    $this->a_validationErrors = array_merge($this->a_validationErrors, $this->validation->getErrors());
    
    if (!$this->bo_throwError) {
      return $this->a_validationErrors;
    }

    if (count($this->a_validationErrors) > 0) {
      throw new \ValidationException("Error validating : \n" . implode("\n", $this->a_validationErrors));
    }
  }
  
  /**
   * 
   * @return array
   */
  public function getValidationErors()
  {
    return $this->a_validationErrors;
  }

  public function __get($s_key)
  {
    $s_call = ' get' . ucfirst($s_key);
    if (property_exists($this, $s_call)) {
      return $this->$s_call();
    }

    return null;
  }

  public function __($s_key, $s_value)
  {
    $s_call = ' set' . ucfirst($s_key);
    if (property_exist($this, $s_call)) {
      $this->$s_call($s_value);
    }
  }
}
