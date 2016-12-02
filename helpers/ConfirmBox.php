<?php
namespace youconix\core\helpers;

/**
 * Generates a styled confirm box
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 2.0
 */
class ConfirmBox extends \youconix\core\helpers\Helper
{
    /**
     *
     * @var \youconix\core\helpers\HTML
     */
    protected $html;

    /**
     * Constructor
     *     
     * @param \youconix\core\helpers\HTML $html            
     */
    public function __construct(\youconix\core\helpers\HTML $html)
    {
        $this->html = $html;
    }

    /**
     * Creates the confirmbox
     * 
     * @param \Output $template
     */
    public function create(\Output $template)
    {
        $css = $this->html->stylesheetLink('/{{ $shared_style_dir }}css/widgets/confirmbox.css', 'screen');
        $template->append('head',$css->generateItem());
        $javascript = $this->html->javascriptLink('/js/widgets/confirmbox.js');
        $template->append('head',$javascript->generateItem());
    }
}
