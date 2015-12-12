<?php
namespace youconix\core\helpers\html;

class InputFactory
{

	/**
	 * Handle class as singleton
	 *
	 * @return bool
	 */
	public function isSingleton(){
		return true;
	}

	protected function __clone()
	{}

	/**
	 * Generates a new text field
	 *
	 * @param string $s_name
	 *            The name of the field
	 * @param string $s_type
	 *            The type of the field
	 * @param string $s_value
	 *            The default text of the field
	 * @param string $s_htmlType
	 *            type
	 * @throws \Exception If the type is invalid
	 * @return \youconix\core\helpers\html\Input
	 */
	public function input($s_name, $s_type, $s_value, $s_htmlType)
	{
		return new \youconix\core\helpers\html\Input($s_name, $s_type, $s_value, $s_htmlType);
	}

	/**
	 * Creates a range slider
	 *
	 * @param string $s_name
	 *            The name
	 * @param string $s_value
	 *            The value
	 * @return \youconix\core\helpers\html\Range
	 */
	public function range($s_name, $i_value)
	{
		return new \youconix\core\helpers\html\Range($s_name, $i_value);
	}

	/**
	 * Creates a new number field
	 *
	 * @param string $s_name
	 *            The name
	 * @param string $s_value
	 *            The value
	 * @return \youconix\core\helpers\html\Number
	 */
	public function number($s_name, $i_value)
	{
		return new \youconix\core\helpers\html\Number($s_name, $i_value);
	}

	/**
	 * Creates a new date field
	 *
	 * @param string $s_name
	 *            The name
	 * @param string $s_value
	 *            The value
	 * @return \youconix\core\helpers\html\Date
	 */
	public function date($s_name, $i_value)
	{
		return new \youconix\core\helpers\html\Date($s_name, $i_value);
	}

	/**
	 * Creates a new date and time field
	 *
	 * @param string $s_name
	 *            The name of the field
	 * @param bool $bo_local
	 *            Set to true to localize the field
	 * @return \youconix\core\helpers\html\Datetime
	 */
	public function datetime($s_name, $bo_local)
	{
		return new \youconix\core\helpers\html\Datetime($s_name, $bo_local);
	}

	/**
	 * Generates a new button
	 *
	 * @param string $s_name
	 *            The name of the button
	 * @param string $s_type
	 *            The type of the button
	 * @param string $s_value
	 *            The default text of the button
	 * @param string $s_htmlType
	 *            type
	 * @throws \Exception If the type is invalid
	 * @return \youconix\core\helpers\html\Button
	 */
	public function button($s_name, $s_type, $s_value, $s_htmlType)
	{
		return new \youconix\core\helpers\html\Button($s_name, $s_type, $s_value, $s_htmlType);
	}

	/**
	 * Generates a new radio button element
	 *
	 * @param string $s_name
	 * @param string $s_value
	 * @param string $s_htmlType
	 *            type
	 * @return \youconix\core\helpers\html\Radio
	 */
	public function radio($s_name, $s_value, $s_htmlType)
	{
		return new \youconix\core\helpers\html\Radio($s_name, $s_value, $s_htmlType);
	}

	/**
	 * Generates a new checkbox element
	 *
	 * @param string $s_name
	 * @param string $s_value
	 *            value
	 * @param string $s_htmlType
	 *            type
	 * @return \youconix\core\helpers\html\Checkbox
	 */
	public function checkbox($s_name, $s_value, $s_htmlType)
	{
		return new \youconix\core\helpers\html\Checkbox($s_name, $s_value, $s_htmlType);
	}

	/**
	 * Generates a multiply row text input field
	 *
	 * @param string $s_name
	 *            The name of the textarea
	 * @param string $s_value
	 *            The default text of the textarea, optional
	 * @return \youconix\core\helpers\html\Textarea
	 */
	public function textarea($s_name, $s_value = '')
	{
		return new \youconix\core\helpers\html\Textarea($s_name, $s_value);
	}

	/**
	 * Generates a select list
	 *
	 * @param string $s_name
	 *            The name of the select list
	 * @return \youconix\core\helpers\html\Select
	 */
	public function select($s_name)
	{
		return new \youconix\core\helpers\html\Select($s_name);
	}
}