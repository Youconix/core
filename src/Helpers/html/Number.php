<?php
namespace youconix\core\helpers\html;

class Number extends \youconix\core\helpers\html\Range
{

	protected $i_step;

	/**
	 * Creates a new number field
	 *
	 * @param string $s_name
	 *            The name
	 * @param string $s_value
	 *            The value
	 */
	public function __construct($s_name, $s_value)
	{
		parent::__construct($s_name, $s_value);

		$this->s_tag = '<input type="number" name="{name}"{min}{max}{step}{between} value="{value}">';
	}

	/**
	 * Sets the step size
	 *
	 * @param int $i_step
	 *            The size
	 */
	public function setStep($i_step)
	{
		$this->i_step = $i_step;

		return $this;
	}

	/**
	 * Generates the (X)HTML-code
	 *
	 * @see \youconix\core\helpers\html\HtmlFormItem::generateItem()
	 * @return string The (X)HTML code
	 */
	public function generateItem()
	{
		(! is_null($this->i_step)) ? $this->i_step = ' step="' . $this->i_step . '"' : $this->i_step = '';

		$this->s_tag = str_replace('{step}', $this->i_step, $this->s_tag);

		return parent::generateItem();
	}
}