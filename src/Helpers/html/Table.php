<?php
namespace youconix\core\helpers\html;


class Table extends HtmlItem
{

    protected $obj_header = null;

    protected $a_rows;

    protected $obj_footer = null;

    /**
     * Generates a new table element
     */
    public function __construct()
    {
        $this->s_tag = "<table {between}>\n{value}</table>\n";
        
        $this->a_rows = array();
    }

    /**
     * Adds the table header
     *
     * @param \youconix\core\helpers\html\TableRow $obj_row
     *            row
     */
    public function addHeader(\youconix\core\helpers\html\TableRow $obj_row)
    {        
        $this->obj_header = $obj_row;
        return $this;
    }

    /**
     * Sets the content of the table.
     * Overwrites any added content
     *
     * @param \youconix\core\helpers\html\TableRow $obj_row
     *            The row to add
     */
    public function setValue(\youconix\core\helpers\html\Tablerow $obj_row)
    {
        $this->a_rows = array();
        
        return $this->addRow($obj_row);
    }

    /**
     * Adds a row
     *
     * @param \youconix\core\helpers\html\TableRow $obj_row
     *            The row to add
     */
    public function addRow(\youconix\core\helpers\html\TableRow $obj_row)
    {        
        $this->a_rows[] = $obj_row;
        
        return $this;
    }

    /**
     * Adds the table footer
     *
     * @param \youconix\core\helpers\html\TableRow $obj_row
     *            row
     */
    public function addFooter(\youconix\core\helpers\html\TableRow $obj_row)
    {        
        $this->obj_footer = $obj_row;
        
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
        /* Generate header */
        if (! is_null($this->obj_header)) {
            $this->s_value = "<thead>\n" . $this->obj_header->generateItem() . "</thead>\n";
        }
        
        /* Generate rows */
        $this->s_value .= "<tbody>\n";
        foreach ($this->a_rows as $obj_row) {
            $this->s_value .= $obj_row->generateItem();
        }
        $this->s_value .= "</tbody>\n";
        
        /* Generate footer */
        if (! is_null($this->obj_footer)) {
            $this->s_value .= "<tfoot>\n" . $this->obj_footer->generateItem() . "</tfoot>\n";
        }
        
        return parent::generateItem();
    }
}