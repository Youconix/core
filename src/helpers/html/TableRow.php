<?php
namespace youconix\core\helpers\html;

class TableRow extends \youconix\core\helpers\html\HtmlItem
{

	protected $a_cells;

	/**
	 * Generates a new table row element
	 */
	public function __construct()
	{
		$this->s_tag = "<tr {between}>\n{value}</tr>\n";

		$this->a_cells = array();
	}

	/**
	 * Adds a table cell
	 *
	 * @param \youconix\core\helpers\html\TableCell $obj_cell
	 *            The table cell
	 */
	public function addCell(\youconix\core\helpers\html\TableCell $obj_cell)
	{
		$this->a_cells[] = $obj_cell;

		return $this;
	}

	/**
	 * Creates a table cell and adds it
	 *
	 * @param string $s_content
	 *            The content of the cell. Also accepts a subtype of CoreHtmlItem
	 */
	public function createCell($s_content)
	{
		$obj_cell = new \youconix\core\helpers\html\TableCell($s_content);
		$this->a_cells[] = $obj_cell;

		return $this;
	}

	/**
	 * Sets the value(s) of the table row
	 *
	 * @param string/array $s_value
	 *            The value(s) to add
	 */
	public function setValue($s_value)
	{
		if (is_array($s_value)) {
			foreach ($s_value as $s_item) {
				$this->createCell($s_item);
			}
		} else {
			$this->createCell($s_value);
		}

		return $this;
	}

	/**
	 * Generates the (X)HTML-code
	 *
	 * @see \youconix\core\helpers\html\HtmlItem::generateItem()
	 * @return string The (X)HTML code
	 */
	public function generateItem()
	{
		/* Generate row */
		foreach ($this->a_cells as $obj_cell) {
			$this->s_value .= $obj_cell->generateItem() . "\n";
		}

		return parent::generateItem();
	}
}