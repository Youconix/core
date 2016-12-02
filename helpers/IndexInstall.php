<?php
namespace youconix\core\helpers;

class IndexInstall extends Helper implements \Display
{
  /**
   *
   * @var \Output
   */
    protected $view;
    
    /**
     *
     * @var \Config
     */
    protected $config;
    
    public function __construct(\Config $config){
      $this->config = $config;
    }

    /**
     * Generates the HTML code
     *
     * @param \Output $view
     */
    public function generate(\Output $view)
    {
	$this->view = $view;
	
	$view->set('head','<link rel="stylesheet" href="/'.$this->config->getSharedStylesDir().'css/installIndex.css">');
      
        $this->title();
        
        $this->installCompleted();
        
        $this->gettingStarted();
        
        $this->maintenance();
    }

    private function title()
    {
      $this->view->set('index_install_header','Youconix framework');
    }

    private function installCompleted()
    {
      $this->view->set('index_install_completed_1','Youconix framework is a modern framework designed for AJAX-usage with modern techniques like MVC, interfaces, namespaces, dependency injection and unit-testing through the entire core.');
      $this->view->set('index_install_completed_2','You have the freedom the use the code however you want. The framework is to serve you instead of forcing you to a certain way. You can use BaselogicClass for all your pages without any problem.
	But if you only want to core without a GUI? No problem, just call Memory directly and go ahead.');
      $this->view->set('index_install_completed_3','Code on the way you want, but remember : If the framework does not like your commands, it will throw an exception.');
    }

    private function gettingStarted()
    {
      $this->view->set('index_install_getting_started_header','Getting started');
      $this->view->set('index_install_getting_started_1','<a href="http://framework.youconix.nl/2/wiki/controllers">Creating new pages</a> Each page you visit trough the browser has his own controller and own access rights.');
      $this->view->set('index_install_getting_started_2','<a href="http://framework.youconix.nl/2/wiki/models">Creating new models</a> The database access should run trough controllers. A controller is a class that maintains the data and only this class knows how the tables are called.');
      $this->view->set('index_install_getting_started_3','<a href="http://framework.youconix.nl/2/wiki/overrides">Overriding framework classes</a> It is possible to automatically override the framework libaries. The code will not even notice it until you cast it to your own object!');
    }

    private function maintenance()
    {
      $this->view->set('index_install_maintenance_header','External information');
      $this->view->set('index_install_maintenance_1','Dependency injection');
      $this->view->set('index_install_maintenance_2','Exceptions');
      $this->view->set('index_install_maintenance_3','Interface');
      $this->view->set('index_install_maintenance_4','Namespaces');
      $this->view->set('index_install_maintenance_5','Unit testing');
    }
}