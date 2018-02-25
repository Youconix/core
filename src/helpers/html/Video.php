<?php
namespace youconix\core\helpers\html;

class Video extends \youconix\core\helpers\html\Audio
{

    protected $s_poster = '';

    /**
     * Generates a new video element
     *
     * @param string $s_url
     *            url
     * @param string $s_type
     *            video type
     */
    public function __construct($s_url, $s_type)
    {
        $this->a_sources[] = array(
            $s_url,
            $s_type
        );
        
        $this->s_tag = '<video {between}>{value}</video>';
    }

    /**
     * Sets the loading icon
     *
     * @param string $s_icon
     *            icon
     */
    public function setLoader($s_icon)
    {
        $this->s_poster = $s_icon;
        
        return $this;
    }

    /**
     * Sets the preload action
     * Note: The preload attribute is ignored if autoplay is set.
     *
     * @param string $s_action
     *            action (auto|metadata|none)
     */
    public function setPreLoader($s_action)
    {
        if ($s_action == 'auto' || $s_action == 'metadata' || $s_action == 'none') {
            $this->s_preload = $s_action;
        }
        
        return $this;
    }

    /**
     * Generates the sources
     */
    protected function generateSources()
    {
        $this->s_value = '';
        foreach ($this->a_sources as $a_source) {
            $this->s_value .= '<source src="' . $a_source[0] . '" type="video/' . $a_source[1] . '">';
        }
    }

    /**
     * Generates the (X)HTML-code
     *
     * @see \youconix\core\helpers\html\HTML_Audio::generateItem()
     * @return string The (X)HTML code
     */
    public function generateItem()
    {
        /* Generate between */
        if (! empty($this->s_poster))
            $this->s_between .= ' poster="' . $this->s_poster . '"';
        
        return parent::generateItem();
    }
}