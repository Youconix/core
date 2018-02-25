<?php
namespace youconix\core\classes;

/**
 * Site footer
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
 */
class Footer implements \Footer
{

    /**
     * 
     * @var \Output
     */
    protected $template;
    
    /**
     * 
     * @var \Settings
     */
    protected $settings;

    /**
     * Starts the class footer
     * 
     * @param \Output $template
     * @param \Settings  $settings
     */
    public function __construct(\Output $template,\Settings $settings)
    {
        $this->template = $template;
        $this->settings = $settings;
    }

    /**
     * Generates the footer
     */
    public function createFooter()
    {
        $this->template->set('version', $this->settings->get('version'));
    }
}