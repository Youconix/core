<?php
namespace core;

/**
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
include_once (NIV . 'core/BaseLogicClass.php');

abstract class AdminLogicClass extends \core\BaseLogicClass
{
    /**
     * 
     * @var \Psr\Log\LoggerInterface
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
    public function __construct(\Input $Input,\Config $config,\Language $language,\Output $template,\Logger $logs)
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
            throw new \BadMethodCallException('Call to unkown method '.$s_command.' on class '.get_class($this).'.');
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
