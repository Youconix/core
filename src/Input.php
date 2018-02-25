<?php

namespace youconix\Core;

class Input implements \InputInterface, \ArrayAccess
{

  /**
   *
   * @var \Security
   */
  protected $security;

  /**
   *
   * @var \ValidationInterface
   */
  protected $validation;

  protected $type;

  protected $container = [];

  protected $previous = [];

  public function __construct(\SecurityInterface $security, \ValidationInterface $validation)
  {
    $this->security = $security;
    $this->validation = $validation;
  }

  /**
   * Secures the input from the given type
   *
   * @param string $type
   *            The global variable type (POST | GET | REQUEST | SESSION | SERVER )
   * @param array $fields
   */
  public function parse($type, array $fields)
  {
    $this->type = $type;

    $this->container = $this->security->secureInput($type, $fields);
  }

  /**
   * Passes all the given type fields to the request
   * WARNING : DISABLES SECURITY
   *
   * @param string $type
   *            The global variable type (POST | GET | REQUEST | SESSION | SERVER )
   * @return \InputInterface
   */
  public function getAll($type)
  {
    $this->type = $type;

    switch ($type) {
      case 'GET' :
        $this->container = $_GET;
        break;
      case 'POST' :
        $this->container = $_POST;
        break;
      case 'REQUEST' :
        $this->container = $_REQUEST;
        break;
      case 'SESSION' :
        $this->container = $_SESSION;
        break;
      case 'SERVER' :
        $this->container = $_SERVER;
        break;
    }

    return $this;
  }

  /**
   * Checks if the input has the given field
   *
   * @param string $key
   * @return boolean
   */
  public function has($key)
  {
    return array_key_exists($key, $this->container);
  }

  /**
   * Returns the value from the given field
   * Gives the default value if the field does not exist
   *
   * @param string $key
   * @param string $default
   * @return mixed
   */
  public function getDefault($key, $default = '')
  {
    if (!$this->has($key)) {
      return $default;
    }

    return $this->container[$key];
  }

  /**
   * Returns the value from the given field
   *
   * @param string $key
   *            The field name
   * @return mixed
   * @throws \OutOfBoundsException If the field does not exist
   */
  public function get($key)
  {
    if (!$this->has($key)) {
      throw new \OutOfBoundsException('Key ' . $key . ' is not present in collection.');
    }

    return $this->container[$key];
  }

  /**
   * Validates the input
   *
   * @param array $rules
   * @return boolean True if the input is valid
   */
  public function validate(array $rules)
  {
    $a_errors = $this->validateErrors($rules);

    return (count($a_errors) == 0);
  }

  /**
   * Validates the input and returns the validation errors
   *
   * @param array $rules
   * @return array The errors, empty array if the input is valid
   */
  public function validateErrors(array $rules)
  {
    if ($this->validation->validate($rules, $this->container)) {
      return [];
    }

    return $this->validation->getErrors();
  }

  /**
   * Returns the validation service
   *
   * @return \ValidationInterface
   */
  public function getValidation()
  {
    return $this->validation;
  }

  public function offsetSet($offset, $value)
  {
    if (is_null($offset)) {
      $this->container[] = $value;
    } else {
      $this->container[$offset] = $value;
    }
  }

  public function offsetExists($offset)
  {
    return $this->has($offset);
  }

  public function offsetUnset($offset)
  {
    unset($this->container[$offset]);
  }

  public function offsetGet($offset)
  {
    return $this->get($offset);
  }

  /**
   * Returns the current values as array
   *
   * @return array
   */
  public function toArray()
  {
    return $this->container;
  }

  /**
   * Sets the previous request values
   *
   * @param array $data
   */
  public function setPrevious(array $data)
  {
    $this->previous = $data;
  }

  /**
   * Returns the previous request values
   *
   * @return array The values
   */
  public function getPrevious()
  {
    return $this->previous;
  }
}