<?php
namespace core\helpers\html;

class StylesheetLink extends CoreHtmlItem
{

    /**
     * Generates a new stylesheet link element
     *
     * @param String $s_link
     *            The url of the link
     * @param String $s_media
     *            The media type
     * @param String $s_htmlType
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

class Stylesheet extends CoreHtmlItem
{

    /**
     * Generates the new CSS tags element
     *
     * @param String $s_css
     *            The CSS code
     * @param String $s_htmlType
     *            type
     */
    public function __construct($s_css, $s_htmlType)
    {
        $s_type = ' type="text/css"';
        if ($s_htmlType == 'html5') {
            $s_type = '';
        }
        $this->setHtmlType($s_htmlType);
        
        $this->s_tag = "<style" . $s_type . ">\n<!--\n" . $s_css . "\n-->\n</style>\n";
    }
}

class JavascriptLink extends CoreHtmlItem
{

    /**
     * Generates a new Javascript link element
     *
     * @param String $s_link
     *            The url of the link
     * @param String $s_htmlType
     *            type
     */
    public function __construct($s_link, $s_htmlType)
    {
        $s_type = ' type="text/javascript"';
        if ($s_htmlType == 'html5') {
            $s_type = '';
        }
        $this->setHtmlType($s_htmlType);
        
        $this->s_tag = '<script src="' . $s_link . '"' . $s_type . ' {between}></script>' . "\n";
    }
}

class Javascript extends CoreHtmlItem
{

    /**
     * Generates a new Javascript tags element
     *
     * @param String $s_javascript
     *            The Javascript code
     * @param String $s_htmlType
     *            type
     */
    public function __construct($s_javascript, $s_htmlType)
    {
        $s_type = ' type="text/javascript"';
        if ($s_htmlType == 'html5') {
            $s_type = '';
        }
        $this->setHtmlType($s_htmlType);
        
        $this->s_tag .= "<script" . $s_type . ">\n<!--\n" . $s_javascript . "\n//-->\n</script>\n";
    }
}

class Metatag extends CoreHtmlItem
{

    /**
     * Generates a new metatag element
     *
     * @param String $s_name
     *            The name of the metatag
     * @param String $s_content
     *            The content of the metatag
     * @param String $s_scheme
     *            The scheme of the metatag,optional
     * @param String $s_htmlType
     *            type
     */
    public function __construct($s_name, $s_content, $s_scheme, $s_htmlType)
    {
        $this->setHtmlType($s_htmlType);
        if (! empty($s_scheme))
            $s_scheme = ' scheme="' . $s_scheme . ' ';
        
        $s_pre = 'name';
        if (in_array($s_name, array(
            'refresh',
            'charset',
            'expires'
        )))
            $s_pre = 'http-equiv';
        
        if ($s_htmlType == 'xhtml') {
            $this->s_tag = '<meta' . $s_scheme . ' ' . $s_pre . '="' . $s_name . '" content="' . $s_content . '" {between}/>' . "\n";
        } else {
            $this->s_tag = '<meta' . $s_scheme . ' ' . $s_pre . '="' . $s_name . '" content="' . $s_content . '" {between}>' . "\n";
        }
    }
}