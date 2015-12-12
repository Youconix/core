<?php
namespace youconix\core\helpers\html;

class Checkbox extends \youconix\core\helpers\html\Radio
{

	/**
	 * Generates a new checkbox element
	 *
	 * @param string $s_name
	 * @param string $s_value
	 *            value
	 * @param string $s_htmlType
	 *            type
	 */
	public function __construct($s_name, $s_value, $s_htmlType)
	{
		$this->s_value = $s_value;
		$this->s_name = $s_name;
		$this->setHtmlType($s_htmlType);

		if ($s_htmlType == 'xhtml') {
			$this->s_tag = '<input type="checkbox" name="{name}"{value}{checked} {between}/>';
		} else {
			$this->s_tag = '<input type="checkbox" name="{name}"{value}{checked} {between}>';
		}
	}

	/**
	 * Sets the name
	 *
	 * @param string $s_name
	 *            The name of the checkbox
	 */
	public function setName($s_name)
	{
		parent::setValue($s_name);

		return $this;
	}
}