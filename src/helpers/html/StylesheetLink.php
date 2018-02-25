<?php
namespace youconix\core\helpers\html;

class StylesheetLink extends CoreHtmlItem
{

    /**
     * Generates a new stylesheet link element
     *
     * @param string $s_link
     *            The url of the link
     * @param string $s_media
     *            The media type
     * @param string $s_htmlType
     *            type
     */
    public function __construct($s_link, $s_media, $s_htmlType)
    {
        $s_type = ' type="text/css"';
        if ($s_htmlType == 'html5') {
            $s_type = '';
        }
        $this->setHtmlType($s_htmlType);
        
        $s_media = ' media="' . $s_media . '"';
        
        if ($s_htmlType == 'xhtml') {
            $this->s_tag = '<link rel="stylesheet" href="' . $s_link . '"' . $s_type . $s_media . ' {between}/>';
        } else {
            $this->s_tag = '<link rel="stylesheet" href="' . $s_link . '"' . $s_type . $s_media . ' {between}>';
        }
    }
}