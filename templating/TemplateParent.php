<?php

namespace youconix\core\templating;

abstract class TemplateParent {

  /**
   *
   * @var \youconix\core\services\FileHandler
   */
  protected $fileHandler;

  /**
   *
   * @var \Config
   */
  protected $config;
  protected $s_file;
  protected $s_templateDir;
  protected $a_parser = [];
  protected $a_templates = [];
  protected $a_includes = [];
  protected $s_template;
  protected $s_cachefile;
  protected $bo_compression = false;
  protected $s_contentType = 'text/html';

  public function __construct(\youconix\core\services\FileHandler $handler, \Config $config) {
    $this->fileHandler = $handler;
    $this->config = $config;
  }

  /**
   * Loads the template
   * 
   * @param string $s_file
   * @param string $s_templateDir
   */
  public function load($s_file, $s_templateDir) {
    $this->s_file = $s_file;
    $this->s_templateDir = $s_templateDir;

    $this->loadTemplates($s_templateDir.DS.$s_file);

    $s_hash = str_replace(['./', '//'], ['', '/'], $s_file);
    while (strpos($s_hash, '../') !== false) {
      $s_hash = str_replace('../', '', $s_hash);
    }

    $a_dirs = explode(DS,str_replace(['./','../'],['',''],$s_templateDir));

    $this->s_cachefile = realpath($_SERVER['DOCUMENT_ROOT']) . DS . 'files' . DS . 'cache' . DS . 'views' . DS . $a_dirs[1].DS.$s_hash;
    if ( defined('DEBUG') || !$this->checkCache()) {
      $this->parse();

      $this->fileHandler->writeFile($this->s_cachefile, $this->s_template);
    }
  }

  /**
   * Checks the view cache
   * 
   * @return boolean
   */
  protected function checkCache() {
    // check file
    if ( !$this->fileHandler->exists($this->s_cachefile)) {
      return false;
    }
    $file = $this->fileHandler->getFile($this->s_cachefile);
    $newest = 0;
    foreach ($this->a_templates AS $template) {
      if ($template['changed'] > $newest) {
	$newest = $template['changed'];
      }
    }
    foreach ($this->a_includes AS $include) {
      if ($include['changed'] > $newest) {
	$newest = $include['changed'];
      }
    }

    if ($file->getCTime() < $newest) { // outdated
      return false;
    }
    $this->s_template = $this->fileHandler->readFileObject($file);
    return true;
  }

  /**
   * Loads the templates
   */
  abstract protected function loadTemplates($s_file);

  abstract protected function parse();

  /**
   * Checks if gzip compression is available and enables it
   */
  protected function compression() {
    /* Check encoding */
    if (empty($_SERVER['HTTP_ACCEPT_ENCODING'])) {
      return;
    }

    /* Check server wide compression */
    if ((ini_get('zlib.output_compression') == 'On' || ini_get('zlib.output_compression_level') > 0) || ini_get('output_handler') == 'ob_gzhandler') {
      return;
    }

    if (extension_loaded('zlib') && (stripos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== FALSE) && !defined('DEBUG')) {
      ob_start('ob_gzhandler');
      $this->bo_compression = true;
    }
  }

  /**
   * Returns the template directory
   *
   * @deprecated Replaced by core/models/Config:getTemplateDir
   * @return string The template directory
   */
  public function getTemplateDir() {
    if (!\youconix\core\Memory::isTesting()) {
      trigger_error("This function has been deprecated in favour of core/models/Config->getTemplateDir().", E_USER_DEPRECATED);
    }
    return $this->config->getTemplateDir();
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
    if (!\youconix\core\Memory::isTesting()) {
      trigger_error("This function has been deprecated in favour of dedicated functions within this class.", E_USER_DEPRECATED);
    }
    if (is_object($s_link) && is_subclass_of($s_link, 'CoreHtmlItem')) {
      $s_link = $s_link->generateItem();
    } else
    if (is_object($s_link)) {
      throw new \Exception("Only types of CoreHTMLItem or strings can be added.");
    }

    if (strpos($s_link, '<link rel') !== false) {
      $this->setCssLink($s_link);
    } else
    if (stripos($s_link, '<script') !== false) {
      if (stripos($s_link, 'src=') !== false) {
	$this->setJavascript($s_link);
      } else {
	$this->setJavascript($s_link);
      }
    } else
    if (stripos($s_link, '<meta') !== false) {
      $this->setMetaLink($s_link);
    } else
    if (stripos($s_link, '<style') !== false) {
      $this->setCSS($s_link);
    }
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
    \youconix\core\Memory::type('string', $s_url);
    \youconix\core\Memory::type('string', $s_dir);

    if (!\youconix\core\Memory::isTesting()) {
      trigger_error("This function has been deprecated in favour of dedicated functions within this class.", E_USER_DEPRECATED);
    }

    if (substr($s_url, - 4) != '.tpl') {
      $s_url .= '.tpl';
    }

    if (empty($s_dir)) {
      $s_dir = str_replace('.php', '', \youconix\core\Memory::getPage());
    }

    if (!$this->file->exists(NIV . $this->s_templateDir . '/templates/' . $s_dir . '/' . $s_url)) {
      throw new \TemplateException('Can not load template templates/' . $this->s_templateDir . '/' . $s_dir . '/' . $s_url . '.');
    }

    $s_subTemplate = $this->file->readFile(NIV . $this->s_templateDir . '/templates/' . $s_dir . '/' . $s_url);

    return $s_subTemplate;
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
    \youconix\core\Memory::type('string', $s_key);

    if (is_null($this->s_template)) {
      throw new \TemplateException('No template is loaded for ' . $_SERVER['PHP_SELF'] . '.');
    }

    if (!preg_match('#^[a-zA-Z]+#', $s_key)) {
      throw new \TemplateException('Template keys must start with a letter.');
    }

    if (is_object($s_value)) {
      if (($s_value instanceof \youconix\core\helpers\Display)) {
	$s_value = $s_value->generate();
      } else
      if (is_subclass_of($s_value, 'CoreHtmlItem')) {
	$s_value = $s_value->generateItem();
      }
    }

    $this->a_parser[$s_key] = $s_value;
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
  public function append($s_key, $s_value, $bo_override = false){
    \youconix\core\Memory::type('string', $s_key);

    if (is_null($this->s_template)) {
      throw new \TemplateException('No template is loaded for ' . $_SERVER['PHP_SELF'] . '.');
    }

    if (!preg_match('#^[a-zA-Z]+#', $s_key)) {
      throw new \TemplateException('Template keys must start with a letter.');
    }

    if (is_object($s_value)) {
      if (($s_value instanceof \youconix\core\helpers\Display)) {
	$s_value = $s_value->generate();
      } else
      if (is_subclass_of($s_value, 'CoreHtmlItem')) {
	$s_value = $s_value->generateItem();
      }
    }
    
    if( $bo_override || !array_key_exists($s_key, $this->a_parser) ){
      $this->a_parser[$s_key] = $s_value;
    }
    else if( is_array($this->a_parser[$s_key]) ){
      $this->a_parser[$s_key] = array_merge($this->a_parser[$s_key],$s_value);
    }
    else {
      $this->a_parser[$s_key] .= PHP_EOL.$s_value;
    }
  }

  /**
   * Sets an array if key-value pairs
   * 
   * @param array $a_data
   * @throws \TemplateException if no template is loaded yet
   * @throws \Exception if $s_value is not a string and not a subclass of CoreHtmlItem
   */
  public function setArray($a_data) {
    foreach ($a_data AS $key => $value) {
      $this->set($key, $value);
    }
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
  abstract public function writeTemplate($a_keys, $a_values, $s_template);

  /**
   * Prints the page to the screen and pushes it to the visitor
   */
  public function printToScreen() {
    $this->defaultFields();

    foreach ($this->a_parser AS $key => $value) {
      $$key = $value;
    }

    try {
      set_error_handler(function($error, $error_string) {
	throw new \TemplateException('Template parsing error : ' . $error_string);
      }, E_ALL);

      ob_start();
      include($this->s_cachefile);

      $this->s_template = ob_get_contents();
      ob_end_clean();
    } catch (\Exception $e) {
      ob_end_clean();

      $trace = $e->getTrace();
      $message = $e->getMessage() . ' at rule ' . $trace[0]['line'] . ' at view ' . $trace[0]['file'];
      throw new \TemplateException($message, $e);
    }
  }

  /**
   * Displays the if part with the given key
   *
   * @param string $s_key
   *            The key in template
   */
  public function displayPart($s_key) {
    \youconix\core\Memory::type('string', $s_key);

    if (!preg_match('#^[a-zA-Z]+#', $s_key)) {
      throw new \TemplateException('Template keys must start with a letter.');
    }

    $this->set($s_key, true);
  }

  protected function defaultFields() {
    $a_fields = ['title', 'noscript', 'autostart'];
    foreach ($a_fields AS $s_field) {
      if (!array_key_exists($s_field, $this->a_parser)) {
	$this->a_parser[$s_field] = '';
      }
    }
    $this->a_parser['LEVEL'] = LEVEL;
    $this->a_parser['style_dir'] = $this->getStylesDir();
    $this->a_parser['shared_style_dir'] = $this->config->getSharedStylesDir();
    $this->a_parser['NIV'] = $this->config->getBase();
  }
  
  /**
   * Returns the loaded template directory
   *
   * @return string template directory
   */
  public function getStylesDir(){
    return '/styles/'.$this->config->getTemplateDir().'/';
  }

  public function setContentType($s_contentType) {
    $this->s_contentType = $s_contentType;
  }

  public function getContentType() {
    return $this->s_contentType;
  }

  public function getResult() {
    return $this->s_template;
  }

}
