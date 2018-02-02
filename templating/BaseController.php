<?php

namespace youconix\core\templating;

abstract class BaseController
{

  protected $init_post = [];
  protected $init_get = [];
  protected $init_put = [];
  protected $init_delete = [];

  /**
   *
   * @var \youconix\core\templating\ControllerWrapper
   */
  protected $wrapper;
  protected $bo_acceptAllInput = false;

  /**
   * Base class constructor
   *
   * @param \youconix\core\templating\ControllerWrapper $wrapper
   */
  public function __construct(\youconix\core\templating\ControllerWrapper $wrapper)
  {
    $this->wrapper = $wrapper;

    $this->init();
  }

  /**
   * Inits the class BaseClass
   */
  protected function init()
  {
    if (!$this->bo_acceptAllInput) {
      /* Secure input */
      $this->wrapper->initRequest($this->init_get, $this->init_post,
				  $this->init_put, $this->init_delete);
    } else {
      $this->wrapper->acceptAllRequestInput();
    }
  }

  /**
   * 
   * @return \Request
   */
  protected function getRequest()
  {
    return $this->wrapper->getRequest();
  }

  /**
   * 
   * @return \Headers
   */
  protected function getHeaders()
  {
    return $this->wrapper->getHeaders();
  }

  /**
   * 
   * @return \Logger
   */
  protected function getLogger()
  {
    return $this->wrapper->getLogger();
  }

  /**
   * 
   * @return \Language
   */
  protected function getLanguage()
  {
    return $this->wrapper->getLanguage();
  }

  /**
   * Loads the given view into the parser
   *
   * @param string $s_view
   *            The view relative to the template-directory
   * @param array $a_data
   * 		  Data as key-value pair
   * @param string $s_templateDir
   * 		  Override the default template directory
   * @return \Output
   * @throws \TemplateException if the view does not exist
   * @throws \IOException if the view is not readable
   */
  protected function createView($s_view, $a_data = [], $s_templateDir = '')
  {
    $output = $this->wrapper->getOutput();

    $output->load($s_view, $s_templateDir);
    $output->setArray($a_data);
    $this->wrapper->getLayout()->parse($output);

    return $output;
  }

  /**
   * 
   * @param array $a_data
   */
  protected function createJsonResponse(array $a_data)
  {
    $this->getHeaders()->contentType('application/json');
    $this->getHeaders()->printHeaders();

    echo(json_encode($a_data));
    die();
  }
  
  protected function badRequest()
  {
    $this->getHeaders()->http400();
    $this->getHeaders()->printHeaders();
  }
}
