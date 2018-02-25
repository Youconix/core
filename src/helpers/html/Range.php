<?php
namespace youconix\core\helpers\html;

class Range extends \youconix\core\helpers\html\HtmlFormItem
{

	protected $i_min;

	protected $i_max;

	/**
	 * Creates a range slider
	 *
	 * @param string $s_name
	 *            The name
	 * @param string $s_value
	 *            The value
	 */
	public function __construct($s_name, $s_value)
	{
		$this->s_name = $s_name;
		$this->setHtmlType('html5');
		$this->setValue($s_value);

		$this->s_tag = '<input type="range" name="{name}"{min}{max}{between} value="{value}">';
	}

	/**
	 * Sets the minimun value
	 *
	 * @param int $i_value
	 *            The value
	 */
	public function setMinimun($i_value)
	{
		$this->i_min = $i_value;

		return $this;
	}

	/**
	 * Sets the maximun value
	 *
	 * @param int $i_value
	 *            The value
	 */
	public function setMaximun($i_value)
	{
		$this->i_max = $i_value;

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
		(! is_null($this->i_min)) ? $this->i_min = ' min="' . $this->i_min . '"' : $this->i_min = '';
		(! is_null($this->i_min)) ? $this->i_max = ' max="' . $this->i_max . '"' : $this->i_max = '';

		$this->s_tag = str_replace(array(
				'{min}',
				'{max}'
		), array(
				$this->i_min,
				$this->i_max
		), $this->s_tag);

		return parent::generateItem();
	}
}