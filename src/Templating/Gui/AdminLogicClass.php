<?php
namespace youconix\Core\Templating\Gui;

use \youconix\Core\classes\HeaderAdmin AS HeaderAdmin;
use \youconix\Core\Classes\MenuAdmin AS MenuAdmin;

/**
 * General admin GUI parent class
 * This class is abstract and should be inherited by every admin controller with a gui
 *
 * @author Rachelle Scheijen
 * @version 1.0
 * @since 1.0
 * @see Core/BaseClass.php
 */
class AdminLogicClass extends \youconix\core\Templating\Gui\BaseLogicClass
{

    /**
     *
     * @var \LoggerInterface
     */
    protected $logs;

    /**
     * Admin graphic class constructor
     *
     * @param \ConfigInterface $config
     * @param \LanguageInterface $language           
     * @param \youconix\core\classes\HeaderAdmin $header            
     * @param \youconix\core\classes\MenuAdmin $menu            
     * @param \Footer $footer            
     */
    public function __construct(\ConfigInterface $config, \LanguageInterface $language, HeaderAdmin $header, MenuAdmin $menu, \Footer $footer)
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
