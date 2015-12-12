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
abstract class AdminLogicClass extends \youconix\core\templating\gui\BaseLogicClass
{

    /**
     *
     * @var \Logger
     */
    protected $logs;

    /**
     * Admin class constructor
     *
     * @param \Input $Input            
     * @param \Config $config            
     * @param \Language $language            
     * @param \Output $template            
     * @param \Logger $logs            
     */
    public function __construct(\Input $Input, \Config $config, \Language $language, \Output $template, \Logger $logs)
    {
        $this->config = $config;
        $this->language = $language;
        $this->template = $template;
        $this->logs = $logs;
        
        $this->prepareInput($Input);
        
        $this->init();
    }

    /**
     * Routes the controller
     *
     * @see Routable::route()
     */
    public function route($s_command)
    {
        if (! method_exists($this, $s_command)) {
            throw new \BadMethodCallException('Call to unkown method ' . $s_command . ' on class ' . get_class($this) . '.');
        }
        
        $this->$s_command();
    }

    /**
     * Inits the class AdminLogicClass
     *
     * @see BaseLogicClass::init()
     */
    protected function init()
    {
        if (! $this->config->isAjax()) {
            exit();
        }
        
        parent::init();
    }
}

?>
