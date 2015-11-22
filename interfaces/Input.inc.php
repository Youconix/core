<?php
interface Input {
	/**
	 * Secures the input from the given type
	 *
	 * @param string $s_type	The global variable type (POST | GET | REQUEST | SESSION | SERVER )
	 * @param array $a_fields	The input type rules
	 */
	public function parse($s_type,$a_fields);
	
	/**
	 * Checks if the input has the given field
	 *
	 * @param string $s_key	The field name
	 * @return boolean
	 */
	public function has($s_key);
	
	/**
	 * Returns the value from the given field
	 * Gives the default value if the field does not exist
	 *
	 * @param string $s_key	The field name
	 * @param string $s_default	The default value
	 * @return The value
	 */
	public function getDefault($s_key,$s_default = '');
	
	/**
	 * Returns the value from the given field
	 *
	 * @param string $s_key	The field name
	 * @return The value
	 * @throws \OutOfBoundsException If the field does not exist
	 */
	public function get($s_key);
	
	/**
	 * Validates the input
	 *
	 * @param array $a_rules	The validation rules
	 * @return boolean	True if the input is valid
	 */
	public function validate($a_rules);
	
	/**
	 * Validates the input and returns the validation errors
	 *
	 * @param array $a_rules	The validation rules
	 * @return array	The errors, empty array if the input is valid
	 */
	public function validateErrors($a_rules);
	
	/**
	 * Returns the validation service
	 *
	 * @return \Validation
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
	 * @param array $a_data The values
	 */
	public function setPrevious($a_data);
	
	/**
	 * Returns the previous request values
	 *
	 * @return array The values
	 */
	public function getPrevious();
}