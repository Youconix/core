<?php
namespace core\helpers\html;

class Image extends HtmlItem
{

    private $s_title = '';

    /**
     * Generates a new image element
     *
     * @param String $s_url
     *            The url of the image
     * @param String $s_htmlType
     *            type
     */
    public function __construct($s_url, $s_htmlType)
    {
        $this->setHtmlType($s_htmlType);
        if ($s_htmlType == 'xhtml') {
            $this->s_tag = '<img src="' . $s_url . '"{title} alt="{value}" {between}/>';
        } else {
            $this->s_tag = '<img src="' . $s_url . '"{title} alt="{value}" {between}>';
        }
    }

    /**
     * Sets the title text
     *
     * @param String $s_title
     *            The title text
     */
    public function setTitle($s_title)
    {
        $this->s_title = $s_title;
        
        return $this;
    }

    /**
     * Sets the alt text
     *
     * @param String $s_alt
     *            The alternative text (when the image doesn't work/is not supported)
     */
    public function setValue($s_alt)
    {
        return parent::setValue($s_alt);
    }

    /**
     * Generates the (X)HTML-code
     *
     * @see HtmlItem::generateItem()
     * @return String The (X)HTML code
     */
    public function generateItem()
    {
        if (! empty($this->s_title)) {
            $this->s_title = ' title="' . $this->s_title . '"';
        }
        
        $this->s_tag = str_replace('{title}', $this->s_title, $this->s_tag);
        
        return parent::generateItem();
    }
}