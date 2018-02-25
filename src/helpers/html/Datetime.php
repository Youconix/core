<?php
namespace youconix\core\helpers\html;

class Datetime extends \youconix\core\helpers\html\CoreHTML_Input
{

	/**
	 * Creates a new date and time field
	 *
	 * @param string $s_name
	 *            The name of the field
	 * @param bool $bo_local
	 *            Set to true to localize the field
	 */
	public function __construct($s_name, $bo_local)
	{
		$this->s_name = $s_name;

		if ($bo_local) {
			$this->s_tag = '<input type="datetime-local" name="{name}" {between}>';
		} else {
			$this->s_tag = '<input type="datetime" name="{name}" {between}>';
		}
	}
}