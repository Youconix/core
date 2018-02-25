<?php
namespace youconix\core\helpers\html;

class ListCollection extends \youconix\core\helpers\html\HtmlItem
{

	protected $a_fields;

	/**
	 * Generates a new list element
	 *
	 * @param bool $bo_numberd
	 *            when a numberd list is needed, default false
	 */
	public function __construct($bo_numberd)
	{
		if (! $bo_numberd) {
			$this->s_tag = "<ul {between}>{value}</ul>\n";
		} else {
			$this->s_tag = "<ol {between}>{value}</ol>\n";
		}

		$this->a_fields = array();
	}

	/**
	 * Adds a list row item
	 *
	 * @param string/ListItem $s_row
	 *            list row item
	 */
	public function addRow($s_row)
	{
		if (is_object($s_row)) {
			if (! ($s_row instanceof ListItem)) {
				throw new \Exception("Unexpected input in UnList::addRow. Expect string or ListItem");
			}

			$s_row = $s_row->generateItem();
		}

		$this->a_fields[] = $s_row;

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
		$this->s_value = '';
		foreach ($this->a_fields as $s_row) {
			$this->s_value .= $s_row . "\n";
		}

		return parent::generateItem();
	}
}