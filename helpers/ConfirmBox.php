<?php
namespace core\helpers;

class ConfirmBox extends \core\helpers\Helper
{
    /**
     *
     * @var \core\services\Template $service_Template
     */
    private $service_Template;
    
    /**
     * Constructor 
     *  
     * @param \core\services\Template $service_Template
     */
    
    
    public function __construct(\core\services\Template $service_Template)
    {
        $this->service_Template = $service_Template;   
    }
    
    /**
     * Creates the confirmbox
     */
    public function create(){
        $this->service_Template->setCssLink('<link rel="stylesheet" href="{NIV}{shared_style_dir}css/widgets/confirmbox.css" media="screen">');
        $this->service_Template->setJavascriptLink('<script src="{NIV}js/widgets/confirmbox.js"></script>');
    }
}