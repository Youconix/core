<?php
namespace youconix\core\helpers\html;

class Textarea extends \youconix\core\helpers\html\HtmlFormItem
{

	protected $i_rows = 0;

	protected $i_cols = 0;

	/**
	 * Generates a new textarea item
	 *
	 * @param string $s_name
	 * @param string $s_value
	 */
	public function __construct($s_name, $s_value)
	{
		$this->s_name = $s_name;
		$this->s_value = $this->parseContent($s_value);

		$this->s_tag = '<textarea rows="{rows}" cols="{cols}" name="{name}" {between}>{value}</textarea>';
	}

	/**
	 * Sets the number of rows
	 *
	 * @param int $i_rows
	 *            of rows
	 */
	public function setRows($i_rows)
	{
		if ($i_rows >= 0)
			$this->i_rows = $i_rows;

			return $this;
	}

	/**
	 * Sets the number of cols
	 *
	 * @param int $i_cols
	 *            of cols
	 */
	public function setCols($i_cols)
	{
		if ($i_cols >= 0)
			$this->i_cols = $i_cols;

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
		$this->s_tag = str_replace(array(
				'{rows}',
				'{cols}',
				'{name}'
		), array(
				$this->i_rows,
				$this->i_cols,
				$this->s_name
		), $this->s_tag);

		return parent::generateItem();
	}
}