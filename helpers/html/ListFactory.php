<?php
namespace core\helpers\html;

class ListFactory
{

    private static $_instance;

    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new ListFactory();
        }
        
        return self::$_instance;
    }

    public function numberedList()
    {
        $list = new ListCollection(true);
        return $list;
    }

    public function uNumberedList()
    {
        $list = new ListCollection(false);
        return $list;
    }

    public function createItem($s_content)
    {
        return new ListItem($s_content);
    }
}

class ListCollection extends HtmlItem
{

    private $a_fields;

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
     * @param String/ListItem $s_row
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
     * @see HtmlFormItem::generateItem()
     * @return String The (X)HTML code
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

class ListItem extends HtmlItem
{

    /**
     * Generates a new list item element
     *
     * @param String/CoreHtmLItem $s_content
     *            content
     */
    public function __construct($s_content)
    {
        $this->s_tag = '<li {between}>' . $this->parseContent($s_content) . '</li>';
    }
}