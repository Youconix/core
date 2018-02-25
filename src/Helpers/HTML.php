<?php
namespace youconix\core\helpers;


/**
 * Helper for generating (X)HTML-code
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
 */
class HTML extends \youconix\core\helpers\Helper
{

    protected $s_htmlType = 'html5';

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->s_htmlType = null;
    }

    /**
     * Sets the HTML type for the helper
     * Default setting is html5
     *
     * @param String $s_type
     *            The HTML type (html|xhtml|html5)
     */
    public function setHtmlType($s_type)
    {
        $s_type = strtolower($s_type);
        
        if (in_array($s_type, array(
            'html',
            'xhtml',
            'html5'
        ))) {
            $this->htmlType = $s_type;
        }
    }

    /**
     * Generates a div
     *
     * @param string $s_content
     *            The content, optional
     * @return \youconix\core\helpers\html\Div
     */
    public function div($s_content = '')
    {        
        return new \youconix\core\helpers\html\Div($s_content);
    }

    /**
     * Generates a paragraph
     *
     * @param string $s_content
     *            The content of the paragraph
     * @return \youconix\core\helpers\html\Paragraph
     */
    public function paragraph($s_content)
    {        
        return new \youconix\core\helpers\html\Paragraph($s_content);
    }

    /**
     * Generates a multiply row text input field
     *
     * @param string $s_name
     *            The name of the textarea
     * @param string $s_value
     *            The default text of the textarea, optional
     * @return \youconix\core\helpers\html\Textarea
     */
    public function textarea($s_name, $s_value = '')
    {
        $obj_factory = new \youconix\core\helpers\html\InputFactory();
        return $obj_factory->textarea($s_name, $s_value);
    }

    /**
     * Returns the list factory
     *
     * @return \youconix\core\helpers\html\listFactory
     */
    public function ListFactory()
    {
        return \youconix\core\helpers\html\ListFactory::getInstance();
    }

    /**
     * Generates a form
     *
     * @param string $s_link            
     * @param string $s_method
     *            method (get|post)
     * @param boolean $bo_multidata
     *            true for a mixed content form
     * @return \youconix\core\helpers\html\Form
     */
    public function form($s_link, $s_method, $bo_multidata = false)
    {        
        return new \youconix\core\helpers\html\Form($s_link, $s_method, $bo_multidata);
    }

    /**
     * Returns the table factory
     *
     * @return \youconix\core\helpers\html\TableFactory
     */
    public function tableFactory()
    {        
        return \youconix\core\helpers\html\TableFactory::getInstance();
    }

    /**
     * Returns the input factory
     *
     * @return \youconix\core\helpers\html\InputFactory
     */
    public function getInputFactory()
    {        
        $obj_factory = new \youconix\core\helpers\html\InputFactory();
        return $obj_factory;
    }

    /**
     * Generates a text input field
     *
     * @deprecated Use getInputFactory
     *            
     * @param string $s_name
     *            The name of the text field
     * @param string $s_type
     *            The type of the field
     *            (text|password|hidden) for HTML/XHTML
     *            (text|password|hidden|search|email|url|tel|number|range|date|month|week|time|datetime|
     *            datetime-local|color) for HTML5
     * @param string $s_value
     *            The default text of the field, optional
     * @return \youconix\core\helpers\html\Input
     */
    public function input($s_name, $s_type, $s_value = '')
    {
        if (! \youconix\core\Memory::isTesting()) {
            trigger_error("This function has been deprecated in favour of getInputFactory().", E_USER_DEPRECATED);
        }
        
        $obj_factory =new \youconix\core\helpers\html\InputFactory();
        return $obj_factory->input($s_name, $s_type, $s_value, $this->s_htmlType);
    }

    /**
     * Generates a button
     *
     * @deprecated Use getInputFactory
     *            
     * @param string $s_value
     *            The text on the button
     * @param string $s_name
     *            The name of the button, leave empty for no name
     * @param string $s_type
     *            The type of the button (button|reset|submit)
     * @return \youconix\core\helpers\html\Button
     */
    public function button($s_value, $s_name, $s_type)
    {
        if (! \youconix\core\Memory::isTesting()) {
            trigger_error("This function has been deprecated in favour of getInputFactory().", E_USER_DEPRECATED);
        }
        
        $obj_factory = new \youconix\core\helpers\html\InputFactory();
        return $obj_factory->button($s_value, $s_name, $s_type, $this->bo_xhtml);
    }

    /**
     * Generates a link for linking to other pages
     *
     * @param string $s_url
     *            The url the link has to point to
     * @param string $s_value
     *            The link text, optional
     * @return \youconix\core\helpers\html\Link
     */
    public function link($s_url, $s_value = '')
    {        
        return new \youconix\core\helpers\html\Link($s_url, $s_value);
    }

    /**
     * Generates a image
     *
     * @param string $s_url
     *            The source url from the image
     * @return \youconix\core\helpers\html\Image
     */
    public function image($s_url)
    {
        return new \youconix\core\helpers\html\Image($s_url, $this->s_htmlType);
    }

    /**
     * Generates a header
     *
     * @param int $i_level
     *            The type of header (1|2|3|4|5)
     * @param string $s_content
     *            The content of the header
     * @return \youconix\core\helpers\html\Header
     */
    public function header($i_level, $s_content)
    {   
        return new \youconix\core\helpers\html\Header($i_level, $s_content);
    }

    /**
     * Generates a radio button
     *
     * @param string $s_name            
     * @param string $s_value
     *            value
     * @return \youconix\core\helpers\html\Radio
     */
    public function radio($s_name = '', $s_value = '')
    {        
        return new \youconix\core\helpers\html\Radio($s_name, $s_value, $this->s_htmlType);
    }

    /**
     * Generates a checkbox
     *
     * @param string $s_name            
     * @param string $s_value
     *            value
     * @return \youconix\core\helpers\html\Checkbox
     */
    public function checkbox($s_name = '', $s_value = '')
    {        
        return new \youconix\core\helpers\html\Checkbox($s_name, $s_value, $this->s_htmlType);
    }

    /**
     * Generates a select list
     *
     * @param string $s_name
     *            The name of the select list
     * @return \youconix\core\helpers\html\Select
     */
    public function select($s_name)
    {        
        $obj_factory = new \youconix\core\helpers\html\InputFactory();
        return $obj_factory->Select($s_name);
    }

    /**
     * Generates a link to a external stylesheet
     *
     * @param string $s_link
     *            The link too the css-file
     * @param string $s_media
     *            The media where the stylesheet is for, optional (screen|print|mobile)
     * @return \youconix\core\helpers\html\StylesheetLink
     */
    public function stylesheetLink($s_link, $s_media = 'screen')
    {        
        return new \youconix\core\helpers\html\StylesheetLink($s_link, $s_media, $this->s_htmlType);
    }

    /**
     * Generates the stylesheet tags for inpage CSS
     *
     * @param string $s_css
     *            The css-content
     * @return \youconix\core\helpers\html\Stylesheet
     */
    public function stylesheet($s_css)
    {        
        return new \youconix\core\helpers\html\Stylesheet($s_css, $this->s_htmlType);
    }

    /**
     * Generates a link to a external javascript file
     *
     * @param string $s_link
     *            The link to the javascript-file
     * @return \youconix\core\helpers\html\JavascriptLink
     */
    public function javascriptLink($s_link)
    {        
        return new \youconix\core\helpers\html\JavascriptLink($s_link, $this->s_htmlType);
    }

    /**
     * Generates the javascript tags for inpage javascript
     *
     * @param string $s_javascript
     *            The javascript-content
     * @return \youconix\core\helpers\html\Javascript
     */
    public function javascript($s_javascript)
    {        
        return new \youconix\core\helpers\html\Javascript($s_javascript, $this->s_htmlType);
    }

    /**
     * Generates a meta tag
     *
     * @param string $s_name
     *            The name of the meta tag
     * @param string $s_content
     *            The content of the meta tag
     * @param string $s_scheme
     *            The optional scheme of the meta tag
     * @return \youconix\core\helpers\html\Metatag
     */
    public function metatag($s_name, $s_content, $s_scheme = '')
    {        
        return new \youconix\core\helpers\html\Metatag($s_name, $s_content, $s_scheme, $this->s_htmlType);
    }

    /**
     * Generates a span
     *
     * @param string $s_content
     *            The content of the span
     * @return \youconix\core\helpers\html\Span
     */
    public function span($s_content)
    {        
        return new \youconix\core\helpers\html\Span($s_content);
    }

    /**
     * Generates a audio object
     * HTML 5 only
     *
     * @param string $s_url
     *            audio url
     * @param string $s_type
     *            audio type, default ogg
     * @return \youconix\core\helpers\html\Audio
     */
    public function audio($s_url, $s_type = 'ogg')
    {
        if ($this->s_htmlType != 'html5')
            throw new \Exception("Audio is only supported in HTML 5");
                
        return new \youconix\core\helpers\html\Audio($s_url, $s_type);
    }

    /**
     * Generates a video object
     * HTML 5 only
     *
     * @param string $s_url
     *            source url
     * @param string $s_type
     *            video type, default WebM
     * @return \youconix\core\helpers\html\Video
     */
    public function video($s_url, $s_type = 'WebM')
    {
        if ($this->s_htmlType != 'html5')
            throw new \Exception("Video is only supported in HTML 5");
                
        return new \youconix\core\helpers\html\Video($s_url, $s_type);
    }

    /**
     * Generates a canvas object
     * HTML 5 only
     *
     * @return \youconix\core\helpers\html\Canvas
     */
    public function canvas()
    {
        if ($this->s_htmlType != 'html5')
            throw new \Exception("Canvas is only supported in HTML 5");
                
        return new \youconix\core\helpers\html\Canvas();
    }

    /**
     * Generates a header object
     * HTML 5 only
     *
     * @param string $s_content
     *            content
     * @return \youconix\core\helpers\html\Header
     */
    public function pageHeader($s_content = '')
    {
        if ($this->s_htmlType != 'html5')
            throw new \Exception("Header is only supported in HTML 5");
                
        return new \youconix\core\helpers\html\PageHeader($s_content);
    }

    /**
     * Generates a footer object
     * HTML 5 only
     *
     * @param string $s_content
     *            content
     * @return \youconix\core\helpers\html\Footer
     */
    public function pageFooter($s_content = '')
    {
        if ($this->s_htmlType != 'html5')
            throw new \Exception("Footer is only supported in HTML 5");
                
        return new \youconix\core\helpers\html\Footer($s_content);
    }

    /**
     * Generates a navigation object
     * HTML 5 only
     *
     * @param string $s_content
     *            content
     * @return \youconix\core\helpers\html\Nav
     */
    public function navigation($s_content = '')
    {
        if ($this->s_htmlType != 'html5')
            throw new \Exception("Navigation is only supported in HTML 5");
        
        return new \youconix\core\helpers\html\Nav($s_content);
    }

    /**
     * Generates an article object
     * HTML 5 only
     *
     * @param string $s_content
     *            content
     * @return \youconix\core\helpers\html\Article
     */
    public function article($s_content)
    {
        if ($this->s_htmlType != 'html5')
            throw new \Exception("Article is only supported in HTML 5");
        
        return new \youconix\core\helpers\html\Article($s_content);
    }

    /**
     * Generates a section object
     * HTML 5 only
     *
     * @param string $s_content
     *            content
     * @return \youconix\core\helpers\html\Article
     */
    public function section($s_content)
    {
        if ($this->s_htmlType != 'html5')
            throw new \Exception("Section is only supported in HTML 5");
        
        return new \youconix\core\helpers\html\Section($s_content);
    }
}