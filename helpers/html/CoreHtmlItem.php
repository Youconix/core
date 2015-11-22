<?php
namespace core\helpers\html;

/**
 * Core HTML class
 */
abstract class CoreHtmlItem
{

    protected $s_tag;

    protected $s_between = '';

    protected $s_value = '';

    protected $s_id = '';

    protected $s_htmlType;

    protected $a_data = array();

    protected $s_rel = '';

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->s_tag = null;
        $this->s_between = null;
        $this->s_value = null;
        $this->s_id = null;
        $this->s_htmlType = null;
        $this->a_data = null;
        $this->s_rel = null;
    }

    /**
     * Parses the incoming content
     *
     * @param String/CoreHTMLItem $s_content
     *            content
     * @throws Exception if the type is incompatible
     * @return String parses content
     */
    protected function parseContent($s_content)
    {
        if (is_object($s_content)) {
            if (! is_subclass_of($s_content, 'CoreHtmlItem')) {
                throw new \Exception("Only types of CoreHTMLItem can be automaticly parsed.");
            } else {
                $s_content = $s_content->generateItem();
            }
        }

        return $s_content;
    }

    /**
     * Sets the id on the item.
     * Overwrites the id if a id is allready active
     *
     * @param String $s_id
     *            ID
     */
    public function setID($s_id)
    {
        $this->s_id = $s_id;

        return $this;
    }

    /**
     * Sets a data item
     * HTML 5 only
     *
     * @param String $s_name
     *            name
     * @param String $s_value
     *            value
     */
    public function setData($s_name, $s_value)
    {
        if ($this->s_htmlType == 'html5') {
            $this->a_data[] = array(
                $s_name,
                $s_value
            );
        }

        return $this;
    }

    /**
     * Sets the rel-attribute
     *
     * @param String $s_relation
     *            value
     */
    public function setRelation($s_relation)
    {
        $this->s_rel = $s_relation;

        return $this;
    }

    /**
     * Sets the HTML type
     *
     * @param String $s_type
     *            html type
     */
    protected function setHtmlType($s_type)
    {
        $this->s_htmlType = $s_type;

        return $this;
    }

    /**
     * Generates the (X)HTML-code
     *
     * @return String The (X)HTML code
     */
    public function generateItem()
    {
        if (! empty($this->s_id)) {
            $this->s_between .= ' id="' . trim($this->s_id) . '"';
        }

        $s_data = '';
        foreach ($this->a_data as $a_item) {
            $s_data .= ' data-' . $a_item[0] . '="' . $a_item[1] . '"';
        }
        if (! empty($s_data)) {
            $this->s_between .= $s_data;
        }

        if (! empty($this->s_rel)) {
            $this->s_between .= ' rel="' . $this->s_rel . '"';
        }

        $s_value = str_replace(array(
            '{value}',
            '{between}'
        ), array(
            $this->s_value,
            trim($this->s_between)
        ), $this->s_tag);

        return $s_value;
    }
}

/**
 * HTML parent class
 */
abstract class HtmlItem extends CoreHtmlItem
{

    protected $a_eventName = array();

    protected $a_eventValue = array();

    protected $s_style = '';

    protected $s_class = '';

    protected $s_javascript = '';

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->a_eventName = null;
        $this->a_eventValue = null;
        $this->s_style = null;
        $this->s_class = null;
        $this->s_javascript = null;

        parent::__destruct();
    }

    /**
     * Sets the given event on the item
     *
     * @param String $s_name
     *            event name
     * @param String $s_value
     *            event value
     */
    public function setEvent($s_name, $s_value)
    {
        $this->a_eventName[] = $s_name;
        $this->a_eventValue[] = $s_value;

        return $this;
    }

    /**
     * Sets the style on the item.
     * Adds the style if a style is allready active
     *
     * @param String $s_style
     *            style
     */
    public function setStyle($s_style)
    {
        if (! empty($this->s_style))
            $this->s_style .= '; ';
        $this->s_style .= $s_style;

        return $this;
    }

    /**
     * Sets the class on the item.
     * Adds the class if a class is allready active
     *
     * @param String $s_class
     *            class
     */
    public function setClass($s_class)
    {
        if (! empty($this->s_class))
            $this->s_class .= ' ';
        $this->s_class .= $s_class;

        return $this;
    }

    /**
     * Sets the value on the item.
     * Adds the value if a value is allready set
     *
     * @param String $s_value
     *            value
     */
    public function setValue($s_value)
    {
        $s_value = $this->parseContent($s_value);

        $this->s_value .= $s_value;

        return $this;
    }

    /**
     * Generates the (X)HTML-code
     *
     * @see CoreHtmlItem::generateItem()
     * @return String The (X)HTML code
     */
    public function generateItem()
    {
        $this->s_javascript = '';
        for ($i = 0; $i < count($this->a_eventName); $i ++) {
            $this->s_javascript .= $this->a_eventName[$i] . '="' . $this->a_eventValue[$i] . '" ';
        }

        if (! empty($this->s_style)) {
            $this->s_between .= 'style="' . trim($this->s_style) . '"';
        }
        if (! empty($this->s_class)) {
            $this->s_between .= ' class="' . trim($this->s_class) . '"';
        }
        if (! empty($this->s_javascript)) {
            $this->s_between .= ' ' . trim($this->s_javascript);
        }

        return parent::generateItem();
    }
}

/**
 * HTML form parent class
 */
abstract class HtmlFormItem extends HtmlItem
{

    private $bo_disabled = false;

    protected $s_name;

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->bo_disabled = null;
        $this->s_name = null;

        parent::__destruct();
    }

    /**
     * Enables or disables the item
     *
     * @param Boolean $bo_disabled
     *            to true to disable the item
     */
    public function setDisabled($bo_disabled)
    {
        $this->bo_disabled = $bo_disabled;
    }

    /**
     * Generates the (X)HTML-code
     *
     * @see HtmlItem::generateItem()
     * @return String The (X)HTML code
     */
    public function generateItem()
    {
        if ($this->bo_disabled) {
            $this->s_between .= ' disabled="disabled"';
        }

        $this->s_tag = str_replace('{name}', $this->s_name, $this->s_tag);

        return parent::generateItem();
    }
}