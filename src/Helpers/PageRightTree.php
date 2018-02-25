<?php
namespace youconix\core\helpers;

class PageRightTree extends \youconix\core\helpers\Helper {
  /**
   *
   * @var \Builder
   */
  protected $builder;

  /**
   *
   * @var \ConfigInterface
   */
  protected $config;

  protected $a_tree = [];

  protected $a_locked;

  /**
   * Constructor
   * 
   * @param \Builder $builder
   * @param \ConfigInterface $config
   */
  public function __construct(\Builder $builder,\ConfigInterface $config){
    $this->builder = $builder;
    $this->config = $config;
  }

  /**
   * Creates the tree
   *
   * @param array $a_pages
   */
  public function generate($a_pages){
    $this->getPages();

    $s_root = $_SERVER['DOCUMENT_ROOT'];
    if( !empty($this->config->getBase()) && ($this->config->getBase() != DS) ){
      $s_root .= DS.$this->config->getBase();
    }
    $this->a_pages = $this->generateTree($a_pages,$s_root.DS,'');
  }

  /**
   * Generates a tree branch
   *
   * @param array $a_files    The files
   * @param string $s_root    The files root
   * @param string $s_parent  The parent directory
   * @return array
   */
  protected function generateTree($a_files, $s_root, $s_parent){
    $a_pages = [];

    foreach ($a_files as $key => $file) {
      if (is_numeric($key)) {
        $s_url = str_replace($s_root, '', $file->getPathname());

        $s_image = '';
        if( in_array($s_url,$this->a_locked) ){
          $s_image = '<img src="/{{ $shared_style_dir }}/images/locked.png" alt=""/>';
        }

        if (substr($s_url, 0, 1) != DS) {
          $s_url = DS.$s_url;
        }
        $item = new \stdClass();
        $item->url = $s_url;
        $item->text = $file->getBaseName();
        $item->image = $s_image;

        $a_pages[] = $item;
      } else {
        $a_pages[$key] = $this->generateTree($a_files[$key], $s_root,$s_parent.DS.$key);
      }
    }

    return $a_pages;
  }

  /**
   * Displays the document tree
   *
   * @return string
   */
  public function display(){    
    $s_tree = $this->displayTree($this->a_pages,'','');

    return $s_tree;
  }

  /**
   * Creates a document tree branch
   *
   * @param array $a_files
   * @param string $s_root
   * @param string $s_parent
   * @return string
   */
  protected function displayTree($a_files,$s_root,$s_parent){
    $s_tree = '';
    foreach ($a_files as $key => $file) {
      if( is_array($file) ){
        if( count($file) == 0 ){
          continue;
        }

        $s_tree .= '<li><span class="directory_pointer" data-url="'.$s_parent.DS.$key.'">'.$key.'</span><ul>
              '.$this->displayTree($a_files[$key], $s_root,
                $s_parent.DS.$key).'
              </ul>'."\n";
      }
      else {
        $s_tree .= '<li data-url="'.$file->url.'" class="link">'.$file->image.''.$file->text."</li>\n";
      }
    }

    return $s_tree;
  }

  /**
   * Loads the locked down pages
   */
  protected function getPages(){
    $database = $this->builder->select('group_pages','*')->getResult();
    $a_data = $database->fetch_assoc();

    $this->a_locked = [];
    foreach($a_data AS $a_item){
      if( in_array($a_item['page'],$this->a_locked) ){
        continue;
      }
      if( $a_item['minLevel'] == -1 ){
        continue;
      }

      $this->a_locked[] = $a_item['page'];
    }
  }
}

