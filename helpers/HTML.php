<?php
namespace core\helpers;

use core\helpers\Helper;

/**
 * Miniature-happiness is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Miniature-happiness is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Miniature-happiness. If not, see <http://www.gnu.org/licenses/>.
 *
 * Helper for generating (X)HTML-code
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
 */
class HTML extends Helper
{

    protected $s_htmlType = 'html5';
    
    public function __construct(){
        require_once(NIV.'core/helpers/html/CoreHtmlItem.php');
    }

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
     * @param String $s_content
     *            The content, optional
     * @return Div The Div object
     */
    public function div($s_content = '')
    {
        $this->checkClass('Div');
        
        return new \core\helper\html\Div($s_content);
    }

    /**
     * Generates a paragraph
     *
     * @param String $s_content
     *            The content of the paragraph
     * @return Paragraph The Paragraph object
     */
    public function paragraph($s_content)
    {
        $this->checkClass('Paragraph');
        
        return new \core\helper\html\Paragraph($s_content);
    }

    /**
     * Generates a multiply row text input field
     *
     * @param String $s_name
     *            The name of the textarea
     * @param String $s_value
     *            The default text of the textarea, optional
     * @return Textarea The Textarea object
     */
    public function textarea($s_name, $s_value = '')
    {
        $this->checkClass('Input');
        
        $obj_factory = \core\helpers\html\InputFactory::getInstance();
        return $obj_factory->textarea($s_name, $s_value);
    }

    /**
     * Returns the list factory
     *
     * @return listFactory The list factory
     */
    public function ListFactory()
    {
        $this->checkClass('ListFactory');
        
        return \core\helpers\html\ListFactory::getInstance();
    }

    /**
     * Generates a form
     *
     * @param String $s_link            
     * @param String $s_method
     *            method (get|post)
     * @param Boolean $bo_multidata
     *            true for a mixed content form
     * @return Form The Form object
     */
    public function form($s_link, $s_method, $bo_multidata = false)
    {
        $this->checkClass('Form');
        
        return new \core\helper\html\Form($s_link, $s_method, $bo_multidata);
    }

    /**
     * Returns the table factory
     *
     * @return TableFactory The table object
     */
    public function tableFactory()
    {
        $this->checkClass('TableFactory', 'Table');
        
        return \core\helpers\html\TableFactory::getInstance();
    }

    /**
     * Returns the input factory
     *
     * @return InputFactory The factory
     */
    public function getInputFactory()
    {
        $this->checkClass('Input');
        
        $obj_factory = \core\helpers\html\InputFactory::getInstance();
        return $obj_factory;
    }

    /**
     * Generates a text input field
     *
     * @deprecated Use getInputFactory
     *            
     * @param String $s_name
     *            The name of the text field
     * @param String $s_type
     *            The type of the field
     *            (text|password|hidden) for HTML/XHTML
     *            (text|password|hidden|search|email|url|tel|number|range|date|month|week|time|datetime|
     *            datetime-local|color) for HTML5
     * @param String $s_value
     *            The default text of the field, optional
     * @return Input The Input object
     */
    public function input($s_name, $s_type, $s_value = '')
    {
        if (! \core\Memory::isTesting()) {
            trigger_error("This function has been deprecated in favour of getInputFactory().", E_USER_DEPRECATED);
        }
        $this->checkClass('Input');
        
        $obj_factory =\core\helpers\html\InputFactory::getInstance();
        return $obj_factory->input($s_name, $s_type, $s_value, $this->s_htmlType);
    }

    /**
     * Generates a button
     *
     * @deprecated Use getInputFactory
     *            
     * @param String $s_value
     *            The text on the button
     * @param String $s_name
     *            The name of the button, leave empty for no name
     * @param String $s_type
     *            The type of the button (button|reset|submit)
     * @return Button The Button object
     */
    public function button($s_value, $s_name, $s_type)
    {
        if (! \core\Memory::isTesting()) {
            trigger_error("This function has been deprecated in favour of getInputFactory().", E_USER_DEPRECATED);
        }
        $this->checkClass('Input');
        
        $obj_factory = \core\helpers\html\InputFactory::getInstance();
        return $obj_factory->button($s_value, $s_name, $s_type, $this->bo_xhtml);
    }

    /**
     * Generates a link for linking to other pages
     *
     * @param String $s_url
     *            The url the link has to point to
     * @param String $s_value
     *            The link text, optional
     * @return Link The Link object
     */
    public function link($s_url, $s_value = '')
    {
        $this->checkClass('Link');
        
        return new \core\helper\html\Link($s_url, $s_value);
    }

    /**
     * Generates a image
     *
     * @param String $s_url
     *            The source url from the image
     * @return Image The Image object
     */
    public function image($s_url)
    {
        $this->checkClass('Image');
        
        return new \core\helper\html\Image($s_url, $this->s_htmlType);
    }

    /**
     * Generates a header
     *
     * @param int $i_level
     *            The type of header (1|2|3|4|5)
     * @param String $s_content
     *            The content of the header
     * @return Header The Header object
     */
    public function header($i_level, $s_content)
    {
        $this->checkClass('Header');
        
        return new \core\helper\html\Header($i_level, $s_content);
    }

    /**
     * Generates a radio button
     *
     * @param String $s_name            
     * @param String $s_value
     *            value
     * @return Radio The Radio button object
     */
    public function radio($s_name = '', $s_value = '')
    {
        $this->checkClass('Radio', 'Input');
        
        return new \core\helper\html\Radio($s_name, $s_value, $this->s_htmlType);
    }

    /**
     * Generates a checkbox
     *
     * @param String $s_name            
     * @param String $s_value
     *            value
     * @return Checkbox The Checkbox object
     */
    public function checkbox($s_name = '', $s_value = '')
    {
        $this->checkClass('Checkbox', 'Input');
        
        return new \core\helper\html\Checkbox($s_name, $s_value, $this->s_htmlType);
    }

    /**
     * Generates a select list
     *
     * @param String $s_name
     *            The name of the select list
     * @return Select The Select list object
     */
    public function select($s_name)
    {
        $this->checkClass('Input');
        
        $obj_factory = \core\helper\html\InputFactory::getInstance();
        return $obj_factory->Select($s_name);
    }

    /**
     * Generates a link to a external stylesheet
     *
     * @param String $s_link
     *            The link too the css-file
     * @param String $s_media
     *            The media where the stylesheet is for, optional (screen|print|mobile)
     * @return StylesheetLink The Stylesheet link object
     */
    public function stylesheetLink($s_link, $s_media = 'screen')
    {
        $this->checkClass('StylesheetLink', 'Head');
        
        return new \core\helper\html\StylesheetLink($s_link, $s_media, $this->s_htmlType);
    }

    /**
     * Generates the stylesheet tags for inpage CSS
     *
     * @param String $s_css
     *            The css-content
     * @return Stylesheet The generated stylesheet object
     */
    public function stylesheet($s_css)
    {
        $this->checkClass('Stylesheet', 'Head');
        
        return new \core\helper\html\Stylesheet($s_css, $this->s_htmlType);
    }

    /**
     * Generates a link to a external javascript file
     *
     * @param String $s_link
     *            The link to the javascript-file
     * @return JavascriptLink The Javascript link object
     */
    public function javascriptLink($s_link)
    {
        $this->checkClass('JavascriptLink', 'Head');
        
        return new \core\helper\html\JavascriptLink($s_link, $this->s_htmlType);
    }

    /**
     * Generates the javascript tags for inpage javascript
     *
     * @param String $s_javascript
     *            The javascript-content
     * @return Javascript The generated javascript object
     */
    public function javascript($s_javascript)
    {
        $this->checkClass('Javascript', 'Head');
        
        return new \core\helper\html\Javascript($s_javascript, $this->s_htmlType);
    }

    /**
     * Generates a meta tag
     *
     * @param String $s_name
     *            The name of the meta tag
     * @param String $s_content
     *            The content of the meta tag
     * @param String $s_scheme
     *            The optional scheme of the meta tag
     * @return Metatag The metatag object
     */
    public function metatag($s_name, $s_content, $s_scheme = '')
    {
        $this->checkClass('Metatag', 'Head');
        
        return new \core\helper\html\Metatag($s_name, $s_content, $s_scheme, $this->s_htmlType);
    }

    /**
     * Generates a span
     *
     * @param String $s_content
     *            The content of the span
     * @return Span The span object
     */
    public function span($s_content)
    {
        $this->checkClass('Span');
        
        return new \core\helper\html\Span($s_content);
    }

    /**
     * Generates a audio object
     * HTML 5 only
     *
     * @param String $s_url
     *            audio url
     * @param String $s_type
     *            audio type, default ogg
     * @return Audio audio object
     */
    public function audio($s_url, $s_type = 'ogg')
    {
        if ($this->s_htmlType != 'html5')
            throw new \Exception("Audio is only supported in HTML 5");
        
        $this->checkClass('Audio', 'Video');
        
        return new \core\helper\html\Audio($s_url, $s_type);
    }

    /**
     * Generates a video object
     * HTML 5 only
     *
     * @param String $s_url
     *            source url
     * @param String $s_type
     *            video type, default WebM
     * @return Video video object
     */
    public function video($s_url, $s_type = 'WebM')
    {
        if ($this->s_htmlType != 'html5')
            throw new \Exception("Video is only supported in HTML 5");
        
        $this->checkClass('Video');
        
        return new \core\helper\html\Video($s_url, $s_type);
    }

    /**
     * Generates a canvas object
     * HTML 5 only
     *
     * @return Canvas canvas object
     */
    public function canvas()
    {
        if ($this->s_htmlType != 'html5')
            throw new \Exception("Canvas is only supported in HTML 5");
        
        $this->checkClass('Canvas', 'Draw');
        
        return new \core\helper\html\Canvas();
    }

    /**
     * Generates a header object
     * HTML 5 only
     *
     * @param String $s_content
     *            content
     * @return Header header object
     */
    public function pageHeader($s_content = '')
    {
        if ($this->s_htmlType != 'html5')
            throw new \Exception("Header is only supported in HTML 5");
        
        $this->checkClass('PageHeader', 'Div');
        
        return new \core\helper\html\PageHeader($s_content);
    }

    /**
     * Generates a footer object
     * HTML 5 only
     *
     * @param String $s_content
     *            content
     * @return HTML_Footer footer object
     */
    public function pageFooter($s_content = '')
    {
        if ($this->s_htmlType != 'html5')
            throw new \Exception("Footer is only supported in HTML 5");
        
        $this->checkClass('Footer', 'Div');
        
        return new \core\helper\html\Footer($s_content);
    }

    /**
     * Generates a navigation object
     * HTML 5 only
     *
     * @param String $s_content
     *            content
     * @return Nav navigation object
     */
    public function navigation($s_content = '')
    {
        if ($this->s_htmlType != 'html5')
            throw new \Exception("Navigation is only supported in HTML 5");
        
        $this->checkClass('Nav', 'Div');
        
        return new \core\helper\html\Nav($s_content);
    }

    /**
     * Generates an article object
     * HTML 5 only
     *
     * @param String $s_content
     *            content
     * @return Article article object
     */
    public function article($s_content)
    {
        if ($this->s_htmlType != 'html5')
            throw new \Exception("Article is only supported in HTML 5");
        
        $this->checkClass('Nav', 'Div');
        
        return new \core\helper\html\Article($s_content);
    }

    /**
     * Generates a section object
     * HTML 5 only
     *
     * @param String $s_content
     *            content
     * @return Article section object
     */
    public function section($s_content)
    {
        if ($this->s_htmlType != 'html5')
            throw new \Exception("Section is only supported in HTML 5");
        
        $this->checkClass('Nav', 'Div');
        
        return new \core\helper\html\Section($s_content);
    }

    /**
     * Checks if a class is loaded.
     * If not the class is included
     *
     * @param String $s_name
     *            class name
     * @param String $s_name
     *            filename if different from the class name, optional
     */
    private function checkClass($s_name, $s_file = '')
    {
        if (! class_exists('\core\helper\html\\'.$s_name)) {
            if (empty($s_file))
                $s_file = $s_name;
            
            require_once (NIV . 'core/helpers/html/' . $s_file . '.php');
        }
    }
}