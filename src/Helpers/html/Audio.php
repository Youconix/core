<?php
namespace youconix\core\helpers\html;

class Audio extends \youconix\core\helpers\html\HtmlItem
{

    protected $a_sources = array();

    protected $bo_autoplay = false;

    protected $bo_controls = false;

    protected $bo_loop = false;
    
    protected $bo_muted = false;

    protected $s_preload = '';

    /**
     * Generates a new audio element
     *
     * @param string $s_url
     *            url
     * @param string $s_type
     *            audio type
     */
    public function __construct($s_url, $s_type)
    {
        $this->a_sources[] = array(
            $s_url,
            $s_type
        );
        
        $this->s_tag = '<audio {between}>{value}</audio>';
    }

    /**
     * Disabled
     */
    public function setValue($s_name)
    {}

    /**
     * Sets the auto play
     *
     * @param boolean $bo_autoplay
     *            to true to autoplay
     */
    public function autoplay($bo_autoplay)
    {
        $this->bo_autoplay = $bo_autoplay;
        
        return $this;
    }

    /**
     * Shows the controls
     *
     * @param boolean $bo_show
     *            to true to show the controls
     */
    public function controls($bo_show)
    {
        $this->bo_controls = $bo_show;
        
        return $this;
    }

    /**
     * Plays endless
     *
     * @param boolean $bo_loop
     *            to true to play endless
     */
    public function loop($bo_loop)
    {
        $this->bo_loop = $bo_loop;
        
        return $this;
    }

    /**
     * Sets the preload action
     * Note: The preload attribute is ignored if autoplay is set.
     *
     * @param String $s_action
     *            action (auto|metadata|none)
     */
    public function setLoader($s_action)
    {
        if ($s_action == 'auto' || $s_action == 'metadata' || $s_action == 'none')
            $this->s_preload = $s_action;
        
        return $this;
    }

    /**
     * Adds a source
     *
     * @param string $s_url
     *            url
     * @param string $s_type
     *            video type
     */
    public function addSource($s_url, $s_type)
    {
        $this->a_sources[] = array(
            $s_url,
            $s_type
        );
        
        return $this;
    }

    /**
     * Generates the sources
     */
    protected function generateSources()
    {
        $this->s_value = '';
        foreach ($this->a_sources as $a_source) {
            $this->s_value .= '<source src="' . $a_source[0] . '" type="audio/' . $a_source[1] . '">';
        }
    }

    /**
     * Generates the (X)HTML-code
     *
     * @see \youconix\core\helpers\html\HtmlItem::generateItem()
     * @return string The (X)HTML code
     */
    public function generateItem()
    {
        /* Generate sources */
        if ($this->bo_autoplay) {
            $this->s_preload = '';
        }
        
        $this->generateSources();
        
        /* Generate between */
        if ($this->bo_autoplay)
            $this->s_between .= ' autoplay="autoplay"';
        if ($this->bo_controls)
            $this->s_between .= ' controls="controls"';
        if ($this->bo_loop)
            $this->s_between .= ' loop="loop"';
        if ($this->bo_muted)
            $this->s_between .= ' muted="muted"';
        if (! empty($this->s_preload))
            $this->s_between .= ' preload="' . $this->s_preload . '"';
        
        return parent::generateItem();
    }
}