<?php
namespace youconix\core\templating;

abstract class BaseController implements \Routable
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
     * Inits the class BaseClass
     */
    protected function init()
    {
        /* Secure input */
        $this->get = $this->request->initGet($this->init_get);
        $this->post = $this->request->initPost($this->init_post);
    }
}