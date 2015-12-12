<?php
namespace youconix\core\helpers\html;

class Date extends \youconix\core\helpers\html\Range
{

	/**
	 * Creates a new date field
	 *
	 * @param string $s_name
	 *            The name
	 * @param string $s_value
	 *            The value
	 */
	public function __construct($s_name, $s_value)
	{
		parent::__construct($s_name, $s_value);

		$this->s_tag = '<input type="date" name="{name}"{min}{max}{between} value="{value}">';
	}
}