<?php
namespace core\helpers\html;

class Form extends CoreHtmlItem
{

    private $s_eventSubmit = '';

    /**
     * Generates a new form element
     *
     * @param String $s_link
     *            link
     * @param String $s_method
     *            method (get|post)
     * @param Boolean $bo_multidata
     *            true for a multidata form
     */
    public function __construct($s_link, $s_method, $bo_multidata)
    {
        $s_header = 'action="' . $s_link . '" method="' . $s_method . '"';
        if ($bo_multidata) {
            $s_header .= ' enctype="multipart/form-data"';
        }
        
        $this->s_tag = "<form " . $s_header . "{event}>\n{value}\n</form>\n";
    }

    /**
     * Parses the content
     *
     * @param String/CoreHtmLItem $s_content
     *            content
     */
    public function setContent($s_content)
    {
        $this->s_value .= $this->parseContent($s_content);
        
        return $this;
    }

    /**
     * Sets the submit action
     *
     * @param String $s_value
     *            event value
     */
    public function setSubmit($s_value)
    {
        $this->s_eventSubmit = $s_value;
        
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
        if (! empty($this->s_eventSubmit)) {
            $this->s_eventSubmit = ' onsubmit="' . $this->s_eventSubmit . '"';
        }
        $this->s_tag = str_replace('{event}', $this->s_eventSubmit, $this->s_tag);
        
        return parent::generateItem();
    }
}