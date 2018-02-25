<?php
namespace youconix\core\templating\gui;

use \youconix\core\classes\HeaderAdmin AS HeaderAdmin;
use \youconix\core\classes\MenuAdmin AS MenuAdmin;

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
 * @see Core/BaseClass.php
 */
class AdminLogicClass extends \youconix\core\templating\gui\BaseLogicClass
{

    /**
     *
     * @var \Logger
     */
    protected $logs;

    /**
     * Admin graphic class constructor
     *
     * @param \Config $config            
     * @param \Language $language           
     * @param \youconix\core\classes\HeaderAdmin $header            
     * @param \youconix\core\classes\MenuAdmin $menu            
     * @param \Footer $footer            
     */
    public function __construct(\Config $config, \Language $language, HeaderAdmin $header, MenuAdmin $menu, \Footer $footer)
    {
        $this->config = $config;
        $this->header = $header;
        $this->menu = $menu;
        $this->footer = $footer;    
	$this->language = $language;
    }
    
    protected function statistics()
    {
    }
}

?>
