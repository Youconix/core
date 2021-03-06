<?php
namespace youconix\core\helpers\html;

class Span extends \youconix\core\helpers\html\HtmlItem
{

    /**
     * Generates a new span element
     *
     * @param string $s_content
     *            The content of the span
     */
    public function __construct($s_content)
    {
        $this->s_tag = '<span {between}>' . $this->parseContent($s_content) . '</span>';
    }
}