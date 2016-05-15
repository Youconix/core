<?php
namespace core\templating\gui;

/**
 * General GUI parent class
 * This class is abstract and should be inheritanced by every controller with a gui
 *
 * This file is part of Miniature-happiness
 *
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
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @version 1.0
 * @since 1.0
 * @see core/BaseClass.php
 */
class BaseLogicClass 
{
    
    /**
     * @var \Output
     */
    protected $template;
    
    /**
     * @var \Language
     */
    protected $language;
    
    /**
     * @var \Header
     */
    protected $header;
    
    /**
     * @var \Menu 
     */
     protected $menu;
     
     /**
      * @var \Footer
      */
     protected $footer;
     
     /**
      * 
      * @var \Config
      */
     protected $config;

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
    public function __construct(\Config $config,\Language $language,\Output $template,\Header $header, \Menu $menu, \Footer $footer)
    {
        $this->config  = $config;
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
    	if( !$this->config->isAjax() ){
    	    /* Call header */
	        $this->header->createHeader();
	        
	        /* Call Menu */
	        $this->menu->generateMenu();
	        
	        /* Call footer */
	        $this->footer->createFooter();
    	}
    }

    /**
     * Inits the class BaseLogicClass
     *
     * @see BaseClass::init()
     */
    protected function init()
    {        
        $s_language = $this->language->getLanguage();
        $this->template->setJavascriptLink('<script src="{NIV}js/language.php?lang='.$s_language.'" type="text/javascript"></script>');
        
        if (! $this->config->isAjax()) {
            $this->loadView();
        }
        
        /* Call statistics */
        if (! $this->config->isAjax() && stripos($_SERVER['PHP_SELF'], 'admin/') === false)
            require (NIV . 'stats/statsView.php');
    }

    /**
     * Loads the view
     */
    protected function loadView()
    {
        /* Set language and encoding */
        $this->template->set('lang', $this->language->getLanguage());
        $this->template->set('encoding', $this->language->getEncoding());
        if ($this->language->exists('title')) {
            $this->template->set('mainTitle', $this->language->get('title') . ',  ');
        }
    }
}