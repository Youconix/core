<?php
namespace youconix\core\helpers\html;

class TableCell extends \youconix\core\helpers\html\HtmlItem
{

	protected $i_rowspan = 0;

	protected $i_colspan = 0;

	/**
	 * Generates a new table cell element
	 *
	 * @param string $s_value
	 *            The value of the cell. Also accepts a subtype of CoreHtmlItem
	 */
	public function __construct($s_value)
	{
	    if( $s_value instanceof \youconix\core\helpers\html\CoreHtmlItem ){
	        $s_value = $s_value->generateItem();
	    }
	    
		$this->s_tag = "<td {between}{span}>{value}</td>";
		$this->setValue($s_value);
	}

	/**
	 * Sets the rowspan of the table cell
	 *
	 * @param int $i_rowspan
	 *            The rowspan
	 */
	public function setRowspan($i_rowspan)
	{
		if ($i_rowspan >= 0)
			$this->i_rowspan = $i_rowspan;

			return $this;
	}

	/**
	 * Sets the colspan of the table cell
	 *
	 * @param int $i_colspan
	 *            The colspan
	 */
	public function setColspan($i_colspan)
	{
		if ($i_colspan >= 0)
			$this->i_colspan = $i_colspan;

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
		$s_span = '';
		if ($this->i_colspan > 0)
			$s_span .= 'colspan="' . $this->i_colspan . '" ';
			if ($this->i_rowspan > 0)
				$s_span .= 'rowspan="' . $this->i_rowspan . '" ';

				$this->s_tag = str_replace('{span}', $s_span, $this->s_tag);

				return parent::generateItem();
	}
}