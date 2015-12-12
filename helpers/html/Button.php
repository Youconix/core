<?php
namespace youconix\core\helpers\html;

class Button extends \youconix\core\helpers\html\Input
{

	/**
	 * Checks the type
	 *
	 * @param string $s_type
	 *            The type of the field
	 * @throws \Exception If the type is invalid
	 */
	protected function checkType($s_type)
	{
		if (! in_array($s_type, array(
				'button',
				'reset',
				'submit'
		))) {
			throw new \Exception('invalid button type ' . $s_type);
		}
	}
}