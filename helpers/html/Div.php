<?php
namespace core\helpers\html;

class Div extends HtmlItem
{

    /**
     * Generates a new div element
     *
     * @param String $s_content
     *            content
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
     * @param String $s_value
     *            value
     */
    public function setContent($s_content)
    {
        $this->setValue($s_content);
        
        return $this;
    }
}

class PageHeader extends Div
{

    /**
     * Generates a new header element
     *
     * @param String $s_content
     *            content
     */
    public function __construct($s_content)
    {
        $this->s_tag = "<header {between}>\n{value}\n</header>\n";
        
        $this->setContent($s_content);
    }
}

class Footer extends Div
{

    /**
     * Generates a new footer element
     *
     * @param String $s_content
     *            content
     */
    public function __construct($s_content)
    {
        $this->s_tag = "<footer {between}>\n{value}\n</footer>\n";
        
        $this->setContent($s_content);
    }
}

class Nav extends Div
{

    /**
     * Generates a new nav element
     *
     * @param String $s_content
     *            content
     */
    public function __construct($s_content)
    {
        $this->s_tag = "<nav {between}>\n{value}\n</nav>\n";
        
        $this->setContent($s_content);
    }
}

class Article extends Div
{

    /**
     * Generates a new article element
     *
     * @param String $s_content
     *            content
     */
    public function __construct($s_content)
    {
        $this->s_tag = "<article {between}>\n{value}\n</article>\n";
        
        $this->setContent($s_content);
    }
}

class Section extends Div
{

    /**
     * Generates a new section element
     *
     * @param String $s_content
     *            content
     */
    public function __construct($s_content)
    {
        $this->s_tag = "<section {between}>\n{value}\n</section>\n";
        
        $this->setContent($s_content);
    }
}