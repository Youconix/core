<?php
namespace youconix\core\templating\gui;

/**
 * General GUI parent class
 * This class is abstract and should be inheritanced by every controller with a gui
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @version 1.0
 * @since 1.0
 * @see core/BaseClass.php
 */
class BaseLogicClass implements \Layout
{

    /**
     *
     * @var \Output
     */
    protected $template;

    /**
     *
     * @var \Language
     */
    protected $language;

    /**
     *
     * @var \Header
     */
    protected $header;

    /**
     *
     * @var \Menu
     */
    protected $menu;

    /**
     *
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
     * @param \Header $header            
     * @param \Menu $menu            
     * @param \Footer $footer            
     */
    public function __construct(\Config $config, \Language $language, \Header $header, \Menu $menu, \Footer $footer)
    {
        $this->config = $config;
        $this->header = $header;
        $this->menu = $menu;
        $this->footer = $footer;    
	$this->language = $language;
    }
    
    public function parse(\Output $output){
      $this->template = $output;
      
      $s_language = $this->language->getLanguage();
      $this->template->set('head','<script src="/js/language.php?lang=' . $s_language . '" type="text/javascript"></script>');
      
      /* Set language and encoding */
      $this->template->set('lang', $this->language->getLanguage());
      $this->template->set('encoding', $this->language->getEncoding());
      if ($this->language->exists('title')) {
	$this->template->set('mainTitle', $this->language->get('title') . ',  ');
      }
      else {
	$this->template->set('mainTitle','');
      }
      
      /* Call statistics */
      if (! $this->config->isAjax() && stripos($_SERVER['PHP_SELF'], 'admin/') === false)
	require (NIV . 'stats/statsView.php');
      
      $this->header->createHeader($this->template);
      $this->menu->generateMenu($this->template);
      $this->footer->createFooter($this->template);
    }
}
