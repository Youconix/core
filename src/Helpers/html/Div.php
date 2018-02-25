<?php
namespace youconix\core\helpers\html;

class Div extends \youconix\core\helpers\html\HtmlItem
{

    /**
     * Generates a new div element
     *
     * @param string $s_content
     *            content,  also accepts \youconix\Core\helpers\html\CoreHtmlItem
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
     * @param string $s_content
     *            content,  also accepts \youconix\Core\helpers\html\CoreHtmlItem
     */
    public function setContent($s_content)
    {
        $this->setValue($s_content);
        
        return $this;
    }
}