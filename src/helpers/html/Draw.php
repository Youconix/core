<?php
namespace youconix\core\helpers\html;

class Canvas extends \youconix\core\helpers\html\HtmlItem
{

    /**
     * Generates a new canvas element
     */
    public function __construct()
    {
        $this->s_tag = '<canvas {between}></canvas>';
    }
}