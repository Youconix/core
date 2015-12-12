<?php
namespace youconix\core\helpers\html;

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
     * @param string/CoreHTMLItem $s_content
     *            content
     * @throws \Exception if the type is incompatible
     * @return string parses content
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
     * @param string $s_id
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
     * @param string $s_name
     *            name
     * @param string $s_value
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
     * @param string $s_relation
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
     * @param string $s_type
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
     * @return string The (X)HTML code
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