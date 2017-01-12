<?php

namespace youconix\core\classes;

/**
 * Displays the admin menu
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
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
                              \youconix\core\models\ControlPanelModules $controlPanelModules)
  {
    $this->language = $language;
    $this->xml = $xml;
    $this->controlPanelModules = $controlPanelModules;
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

        $tabItem->item_id = $tabItem->id;

        $tabItem->links = $this->setLinks($a_links);
        $menu_tab_content->items[] = $tabItem;
      }
      $this->a_menu_tab_content[] = $menu_tab_content;

      $i ++;
    }

    $this->template->append('head',
        '<script src="/admin/modulesjs.php" type="text/javascript"></script>');
    $this->template->append('head',
        '<link rel="stylesheet" href="/admin/modulescss.php">');
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

      $a_result[] = $data;
    }

    return $a_result;
  }
}