<?php
namespace youconix\core\templating\gui;

/**
 * General admin GUI parent class
 * This class is abstract and should be inheritanced by every admin controller with a gui
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @version 1.0
 * @since 1.0
 * @see core/BaseClass.php
 */
class AdminLogicClass extends \youconix\core\templating\gui\BaseLogicClass
{

    /**
     * Base graphic class constructor
     *
     * @param \Config $config            
     * @param \Language $language            
     * @param \Output $template            
     * @param \Header $header            
     * @param \Menu $menu            
     * @param \Footer $footer  
     */
    public function __construct(\Config $config, \Language $language, \Output $template, \youconix\core\classes\HeaderAdmin $header, \youconix\core\classes\MenuAdmin $menu, \Footer $footer)
    {
        $this->config = $config;
        $this->language = $language;
        $this->template = $template;
        $this->header = $header;
        $this->menu = $menu;
        $this->footer = $footer;
        
        $this->init();
        $this->showLayout();
    }

    /**
     * Shows the header, menu and footer
     */
    protected function showLayout()
    {
        if (! $this->config->isAjax()) {
            // Write header
            $this->header->createHeader();
            
            // Write Menu
            $this->menu->generateMenu();
            
            // Call footer
            $this->footer->createFooter();
        }
    }
}

?>
