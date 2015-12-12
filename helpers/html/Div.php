<?php
namespace youconix\core\helpers\html;

class Div extends HtmlItem
{

    /**
     * Generates a new div element
     *
     * @param string $s_content
     *            content
     */
    public function __construct($s_content)
    {
        $this->s_tag = "<div {between}>{value}</div>";
        
        $this->setContent($s_content);
    }

    /**
     * Sets the content.
     * Adds the value if a value is allready set
     *
     * @param string $s_value
     *            value
     */
    public function setContent($s_content)
    {
        $this->setValue($s_content);
        
        return $this;
    }
}