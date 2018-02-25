<?php

namespace youconix\Core\Templating;

use youconix\Core\Services\FileHandler;

class Template extends \youconix\Core\Services\AbstractService implements \OutputInterface {

  protected $bo_blade = false;

  /** @var \ConfigInterface */
  protected $config;

  /** @var \youconix\Core\Templating\TemplateParent */
  protected $parser;

  /** @var \HeadersInterface */
  protected $headers;
  protected $s_view;

  /** @var string */
  protected $file;

  /** @var string */
  protected $templateDir;

  /** @var \youconix\Core\Services\FileHandler */
  protected $fileHandler;

  public function __construct(FileHandler $fileHandler, \ConfigInterface $config, \HeadersInterface $headers) {
    $this->fileHandler = $fileHandler;
    $this->config = $config;
    $this->headers = $headers;
  }

  /**
   * Loads the given view into the parser
   *
   * @param string $s_view
   *            The view relative to the template-directory
   * @param string $templateDir
   * 		  Override the default template directory
   * @throws \TemplateException if the view does not exist
   * @throws \IOException if the view is not readable
   */
  public function load($view, $templateDir = '') {
    if (empty($templateDir)) {
      $templateDir = $this->config->getTemplateDir();
    }

    $templateDir = NIV . 'styles' . DS . $templateDir . DS . 'templates' . DS;
    $file = $templateDir . DS . $view . '.blade.php';

    $this->templateDir = $templateDir;
    $this->file = $file;

    if (!$this->fileHandler->exists($file) || !$this->fileHandler->isReadable($file)) {
      $file = $templateDir . DS . $view . '.tpl';

      if (!$this->fileHandler->exists($file) || !$this->fileHandler->isReadable($file)) {
	throw new \TemplateException('Template ' . $view . ' in directory ' . $templateDir . ' does not exist.');
      }

      $this->parser = \Loader::inject('\youconix\Core\Templating\TemplateTpl');
    } else {
      $this->parser = \Loader::inject('\youconix\Core\Templating\TemplateBlade');
      $this->bo_blade = true;
    }

    $this->parser->load($file, $templateDir);
    $this->s_view = $view;
  }

  public function __debugInfo()
  {
    return [
      'template loaded' => $this->file,
      'template directory' => $this->templateDir,
      'parser' => get_class($this->parser)
    ];
  }

  /**
   * Returns if the object should be treated as singleton
   *
   * @return boolean True if the object is a singleton
   */
  public static function isSingleton() {
    return true;
  }

  /**
   * Loads a template and returns it as a string
   *
   * @param string $s_url
   *            The URI of the template
   * @param string $s_dir
   *            to search from, optional
   * @return string template
   * @throws \TemplateException if the view does not exist
   * @throws \IOException if the view is not readable
   * @deprecated
   */
  public function loadTemplateAsString($s_url, $s_dir = '') {
    return $this->parser->loadTemplateAsString($s_url, $s_dir);
  }

  /**
   * Sets the given value in the template on the given key
   *
   * @param string $s_key
   *            The key in template
   * @param string/CoreHtmlItem $s_value
   *            The value to write in the template
   * @throws \TemplateException if no template is loaded yet
   * @throws \Exception if $s_value is not a string and not a subclass of CoreHtmlItem
   */
  public function set($s_key, $s_value) {
    $this->parser->set($s_key, $s_value);
  }
  
  /**
     * Appends the given value in the template on the given key
     *
     * @param string $s_key
     *            The key in template
     * @param string/CoreHtmlItem $s_value
     *            The value to write in the template
     * @param bool  Set to true to replace the value
     * @throws \TemplateException if no template is loaded yet
     * @throws \Exception if $s_value is not a string and not a subclass of CoreHtmlItem
     */
    public function append($s_key, $s_value,$bo_override = false){
      $this->parser->append($s_key,$s_value,$bo_override);
    }

  /**
   * Sets an array if key-value pairs
   * 
   * @param array $a_data
   * @throws \TemplateException if no template is loaded yet
   * @throws \Exception if $s_value is not a string and not a subclass of CoreHtmlItem
   */
  public function setArray($a_data) {
    $this->parser->setArray($a_data);
  }

  /**
   * Writes a repeating block to the template
   * @deprecated Use set() instead
   *
   * @param string $s_key
   *            The key in template
   * @param array $a_data
   *            block data
   * @deprecated Use append() instead
   */
  public function setBlock($s_key, $a_data) {
    $this->set($s_key, $a_data);
  }

  /**
   * Displays the if part with the given key
   * @deprecated Use set($s_key,true) instead
   *
   * @param string $s_key
   *            The key in template
   */
  public function displayPart($s_key) {
    $this->parser->displayPart($s_key);
  }

  /**
   * Writes the values to the given keys on the given template
   * @deprecated
   *
   * @param array $a_keys            
   * @param array $a_values            
   * @param string $s_template
   *            The template to parse
   * @return string parsed template
   */
  public function writeTemplate($a_keys, $a_values, $s_template) {
    return $this->parser->writeTemplate($a_keys, $a_values, $s_template);
  }
  
  /**
   * Sets the link to the page-header
   *
   * @param string/CoreHtmlItem $s_link
   *            The link
   * @throws \Exception if $s_link is not a string and not a subclass of CoreHtmlItem
   * @deprecated Use append('head',..) instead
   */
  public function headerLink($s_link) {
    $this->parser->headerLink($s_link);
  }

  /**
   * Prints the page to the screen and pushes it to the visitor
   */
  public function printToScreen() {
    $this->parser->printToScreen();
    
    $this->headers->setHeader('Content-type', $this->parser->getContentType());
    $this->headers->printHeaders();
    echo($this->parser->getResult());
    die();
  }

  public function setContentType($s_contentType) {
    $this->parser->setContentType($s_contentType);
  }
}
