<?php

interface InputInterface
{

    /**
     * Secures the input from the given type
     *
     * @param string $type
     *            The global variable type (POST | GET | REQUEST | SESSION | SERVER )
     * @param array $fields
     *            The input type rules
     */
    public function parse($type, array $fields);
    
    /**
     * Passes all the given type fields to the request
     * WARNING : DISABLES SECURITY
     * 
     * @param string $type
     *            The global variable type (POST | GET | REQUEST | SESSION | SERVER )
     * @return \InputInterface
     */
    public function getAll($type);

    /**
     * Checks if the input has the given field
     *
     * @param string $key
     * @return boolean
     */
    public function has($key);

    /**
     * Returns the value from the given field
     * Gives the default value if the field does not exist
     *
     * @param string $key
     * @param string $default
     * @return mixed
     */
    public function getDefault($key, $default = '');

    /**
     * Returns the value from the given field
     *
     * @param string $key
     * @return mixed
     * @throws \OutOfBoundsException If the field does not exist
     */
    public function get($key);

    /**
     * Validates the input
     *
     * @param array $rules
     * @return boolean True if the input is valid
     */
    public function validate(array $rules);

    /**
     * Validates the input and returns the validation errors
     *
     * @param array $rules
     * @return array The errors, empty array if the input is valid
     */
    public function validateErrors(array $rules);

    /**
     * Returns the validation service
     *
     * @return \ValidationInterface
     */
    public function getValidation();

    /**
     * Returns the current values as array
     *
     * @return array
     */
    public function toArray();

    /**
     * Sets the previous request values
     *
     * @param array $data
     */
    public function setPrevious(array $data);

    /**
     * Returns the previous request values
     *
     * @return array The values
     */
    public function getPrevious();
}