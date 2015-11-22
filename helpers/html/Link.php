<?php
namespace core\helpers\html;

class Link extends HtmlItem
{

    private $s_url = '';

    /**
     * Generates a new link element
     *
     * @param string $s_url
     *            The url of the link
     * @param string $s_value
     *            The value of the link
     */
    public function __construct($s_url, $s_value)
    {
        $this->s_url = $s_url;
        if (empty($s_value)) {
            $this->s_value = $s_url;
        } else {
            $this->setValue($s_value);
        }
        
        $this->s_tag = '<a href="{url}" {between}>{value}</a>';
    }

    /**
     * Generates the (X)HTML-code
     *
     * @see HtmlItem::generateItem()
     * @return string The (X)HTML code
     */
    public function generateItem()
    {
        if (empty($this->s_value))
            $this->s_value = $this->s_url;
        $this->s_tag = str_replace('{url}', $this->s_url, $this->s_tag);
        
        return parent::generateItem();
    }
}