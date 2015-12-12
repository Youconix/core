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
     * @var \Output
     */
    protected $template;

    protected $html;

    /**
     * Constructor
     *
     * @param \Output $template            
     * @param \youconix\core\helpers\HTML $html            
     */
    public function __construct(\Output $template, \youconix\core\helpers\HTML $html)
    {
        $this->template = $template;
        $this->html = $html;
    }

    /**
     * Creates the confirmbox
     */
    public function create()
    {
        $css = $this->html->stylesheetLink('{NIV}{shared_style_dir}css/widgets/confirmbox.css', 'screen');
        $this->template->setCssLink($css->createItem());
        $javascript = $this->html->javascriptLink('{NIV}js/widgets/confirmbox.js');
        $this->template->setJavascriptLink($javascript->createItem());
    }
}