<?php
namespace youconix\core\helpers\html;

class Paragraph extends HtmlItem
{

    /**
     * Generates a new paragraph element
     *
     * @param string $s_content
     *            The content of the paragraph
     */
    public function __construct($s_content)
    {
        $this->s_tag = "<p {between}>{value}</p>\n";
        $this->s_value = $this->parseContent($s_content);
    }
}