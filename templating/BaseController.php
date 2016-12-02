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
     *
     * @var \Layout
     */
    protected $layout;
    
    /**
     *
     * @var \Output
     */
    protected $output;
    
    protected $bo_acceptAllInput = false;

    /**
     * Base class constructor
     *
     * @param \Input $input
     *            The input parser
     * @param \Layout $layout
     *		  The main layout
     * @param \Output $output
     */
    public function __construct(\Request $request,\Layout $layout,\Output $output)
    {
        $this->request = $request;
	$this->layout = $layout;
	$this->output = $output;
        
        $this->init();
    }
    
    /**
     * Inits the class BaseClass
     */
    protected function init()
    {
	if( !$this->bo_acceptAllInput ){
	  /* Secure input */
	  $this->get = $this->request->initGet($this->init_get);
	  $this->post = $this->request->initPost($this->init_post);
	}
	else {
	  $this->get = $this->request->get()->getAll('GET');
	  $this->post = $this->request->get()->getAll('POST');
	}
    }
    
    /**
     * Loads the given view into the parser
     *
     * @param string $s_view
     *            The view relative to the template-directory
     * @param array $a_data
     *		  Data as key-value pair
     * @param string $s_templateDir
     *		  Override the default template directory
     * @return \Output
     * @throws \TemplateException if the view does not exist
     * @throws \IOException if the view is not readable
     */
    protected function createView($s_view,$a_data = [],$s_templateDir = ''){      
      $this->output->load($s_view,$s_templateDir);
      $this->output->setArray($a_data);
      $this->layout->parse($this->output);
      
      return $this->output;
    }
    
    public function getGUI(){
        return '\includes\BaseLogicClass';
    }
}