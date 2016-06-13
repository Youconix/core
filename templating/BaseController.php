<?php
namespace youconix\core\templating;

abstract class BaseController
{

    /**
     *
     * @var \Request
     */
    protected $request;

    protected $init_post = array();

    protected $init_get = array();

    /**
     *
     * @var \Input
     */
    protected $post;

    /**
     *
     * @var \Input
     */
    protected $get;

    /**
     * Base class constructor
     *
     * @param \Input $input
     *            The input parser
     */
    public function __construct(\Request $request)
    {
        $this->request = $request;
        
        $this->init();
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        if (class_exists('\youconix\core\Memory')) {
            \youconix\core\Memory::endProgram();
        }
    }
    
    /**
     * Inits the class BaseClass
     */
    protected function init()
    {
        /* Secure input */
        $this->get = $this->request->initGet($this->init_get);
        $this->post = $this->request->initPost($this->init_post);
    }
    
    public function getGUI(){
        return '\includes\BaseLogicClass';
    }
}