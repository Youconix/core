<?php

namespace youconix\core\classes;

/**
 * Displays the admin menu
 *
 * @since 1.0
 */
class MenuAdmin implements \Menu
{
  /**
   *
   * @var \Language
   */
  private $language;

  /**
   * 
   * @var \youconix\core\models\ControlPanelModules
   */
  private $controlPanelModules;

  /**
   *
   * @var \youconix\core\services\Xml
   */
  private $xml;
  
  /**
   *
   * @var \youconix\core\Routes
   */
  private $routes;

  /**
   * 
   * @var \Output
   */
  private $template;
  private $a_menuTabHeader = [];
  private $a_menu_tab_content = [];

  /**
   * Starts the class menuAdmin
   */
  public function __construct(\Language $language,
                              \youconix\core\services\Xml $xml,
                              \youconix\core\models\ControlPanelModules $controlPanelModules,
			      \youconix\core\Routes $routes)
  {
    $this->language = $language;
    $this->xml = $xml;
    $this->controlPanelModules = $controlPanelModules;
    $this->routes = $routes;
  }

  /**
   * Generates the menu
   *
   * @param \Output $template
   */
  public function generateMenu(\Output $template)
  {
    $this->template = $template;
    $this->modules();

    $this->template->set('menu_tab_header', $this->a_menuTabHeader);
    $this->template->set('menu_tab_content', $this->a_menu_tab_content);
  }

  /**
   * Displays the modules
   */
  private function modules()
  {
    $s_dir = $this->controlPanelModules->getDirectory();
    $a_modules = $this->controlPanelModules->getInstalledModulesList();

    $i = 1;
    foreach ($a_modules as $s_module) {
      $obj_settings = $this->xml->cloneService();
      $obj_settings->load($s_dir.DS.$s_module.'/settings.xml');

      $s_title = $obj_settings->get('module/title');

      ($i == 1) ? $s_class = 'tab_header_active' : $s_class = '';

      $menu_tab_header = new \stdClass();
      $menu_tab_header->class = $s_class;
      $menu_tab_header->id = $i;
      $menu_tab_header->title = $this->language->get($s_title);
      $this->a_menuTabHeader[] = $menu_tab_header;

      $menu_tab_content = new \stdClass();
      $menu_tab_content->id = $i;
      $menu_tab_content->items = [];

      $a_items = $obj_settings->getBlock('module/block');

      foreach ($a_items as $block) {
        $a_links = [];

        $tabItem = new \stdClass();
        $tabItem->item_id = 'admin_'.$i;
        $tabItem->links = [];

        foreach ($block->childNodes as $item) {
          if ($item->tagName == 'link') {

            $a_links[] = $item;
          } else
          if ($item->tagName == 'title') {
            ($this->language->exists($item->nodeValue)) ? $tabItem->title = $this->language->get($item->nodeValue)
                      : $tabItem->title = $item->nodeValue;
          } else {
            $s_name = $item->tagName;
            $tabItem->$s_name = (string) $item->nodeValue;
          }
        }

	$tabItem->path = $this->routes->path($tabItem->path);
        $tabItem->item_id = 'admin_'.$s_module.'_'.strtolower(str_replace(' ','_',$tabItem->title));

        $tabItem->links = $this->setLinks($a_links);
        $menu_tab_content->items[] = $tabItem;
      }
      $this->a_menu_tab_content[] = $menu_tab_content;

      $i ++;
    }
  }

  private function setLinks($a_links)
  {
    $a_result = [];
    foreach ($a_links as $obj_link) {
      $data = new \stdClass();

      foreach ($obj_link->childNodes as $item) {
        if ($item->tagName == 'title') {
          ($this->language->exists($item->nodeValue)) ? $data->title = $this->language->get($item->nodeValue)
                    : $data->title = $item->nodeValue;
        } else {
          $s_name = $item->tagName;
          $data->$s_name = $item->nodeValue;
        }
      }

      $data->link_title = $data->title;
      $data->link_id = $data->id;
      $data->path = $this->routes->path($data->path);

      $a_result[] = $data;
    }

    return $a_result;
  }
}