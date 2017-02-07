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
 * @see core/BaseClass.php
 */
class AdminLogicClass extends \youconix\core\templating\gui\BaseLogicClass
{

    /**
     * Base graphic class constructor
     *
     * @var \Logger
     */
    protected $logs;

    /**
     * Admin graphic class constructor
     *
     * @param \Language $language           
     * @param \youconix\core\classes\HeaderAdmin $header            
     * @param \youconix\core\classes\MenuAdmin $menu            
     * @param \Footer $footer            
     */
    public function __construct(\Language $language, HeaderAdmin $header, MenuAdmin $menu, \Footer $footer)
    {
        $this->header = $header;
        $this->menu = $menu;
        $this->footer = $footer;    
	$this->language = $language;
    }

    /**
     * Shows the statistics
     */
    protected function loadStats(){}
}

?>
