<?php
namespace youconix\core\templating\gui;

use stats\StatsView as Statistics;

/**
 * General GUI parent class
 * This class is abstract and should be inheritanced by every controller with a gui
 *
 * @author Rachelle Scheijen
 * @version 1.0
 * @since 1.0
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
     *
     * @var stats\StatsView
     */
    protected $statistics;

    /**
     * Base graphic class constructor
     *
     * @param \Config $config            
     * @param \Language $language           
     * @param \Header $header            
     * @param \Menu $menu            
     * @param \Footer $footer 
     * @param stats\StatsView $statistics           
     */
    public function __construct(\Config $config, \Language $language, \Header $header, \Menu $menu, \Footer $footer, Statistics $statistics)
    {
        $this->config = $config;
        $this->header = $header;
        $this->menu = $menu;
        $this->footer = $footer;    
	$this->language = $language;
	$this->statistics = $statistics;
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
      
      $this->statistics();
      
      $this->header->createHeader($this->template);
      $this->menu->generateMenu($this->template);
      $this->footer->createFooter($this->template);
    }
    
    protected function statistics()
    {
      $statisticsEnabled = ($this->config->getSettings()->exists('statistics/enabled') ? $this->config->getSettings()->get('statistics/enabled') : 1);
      if (! $this->config->isAjax() && $statisticsEnabled) {
	$this->statistics->generate($this->template);
      }
    }
}