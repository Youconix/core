<?php
namespace core\helpers\html;

class Header extends HtmlItem
{

    /**
     * Generates a new header element
     *
     * @param int $i_level
     *            The type of header (1|2|3|4|5)
     * @param String $s_content
     *            The content of the header
     */
    public function __construct($i_level, $s_content)
    {
        if ($i_level < 1 || $i_level > 5)
            $i_level = 1;
        $this->setValue($s_content);
        
        $this->s_tag = '<h' . $i_level . ' {between}>{value}</h' . $i_level . '>';
    }
}