<?php
namespace core\classes;

/**
 * Miniature-happiness is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Miniature-happiness is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Miniature-happiness. If not, see <http://www.gnu.org/licenses/>.
 *
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
     * @var \core\models\ControlPanelModules
     */
    private $controlPanelModules;

    /**
     * 
     * @var \core\services\Xml
     */
    private $xml;

    /**
     * 
     * @var \Output
     */
    private $template;
    
    private $a_jsItems = array();
    private $a_cssItems = array();

    /**
     * Starts the class menuAdmin
     */
    public function __construct(\Language $language, \core\services\Xml $xml, \Output $template, \core\models\ControlPanelModules $controlPanelModules)
    {
        $this->language = $language;
        $this->xml = $xml;
        $this->controlPanelModules = $controlPanelModules;
        $this->template = $template;
    }
    
    public function generateMenu(){
        $this->modules();
    }

    /**
     * Displays the modules
     */
    private function modules()
    {
        $s_dir = NIV . $this->controlPanelModules->getDirectory();
        $s_coreDir = NIV.$this->controlPanelModules->getCoreDirectory();
        $a_modules = $this->controlPanelModules->getInstalledModulesList();
        
        $i = 1;
        $i_blockNr = 1;
        $a_js = array();
        foreach ($a_modules as $s_module) {
            $obj_settings = $this->xml->cloneService();
            if( in_array($s_module,$this->controlPanelModules->getCoreModules()) ){
                $obj_settings->load($s_coreDir.DS . $s_module . '/settings.xml');
            }
            else {
                $obj_settings->load($s_dir . DS . $s_module . '/settings.xml');
            }
            
            $s_title = $obj_settings->get('module/title');
            $s_jsLink = $obj_settings->get('module/js');
            $s_css = $obj_settings->get('module/css');
            
            $this->setJS($s_module, $s_jsLink);
            $this->setCSS($s_module, $s_css);
            
            ($i == 1) ? $s_class = 'tab_header_active' : $s_class = '';
            $this->template->setBlock('menu_tab_header', array(
                'class' => $s_class,
                'id' => $i,
                'title' => $this->language->get($s_title)
            ));
            
            $this->template->setBlock('menu_tab_content', array(
                'id' => $i
            ));
            
            $a_items = $obj_settings->getBlock('module/block');
            foreach ($a_items as $block) {
                $a_data = array(
                    'id' => $i
                );
                $a_links = array();
                
                foreach ($block->childNodes as $item) {
                    if ($item->tagName == 'link') {
                        $a_links[] = $item;
                    } else 
                        if ($item->tagName == 'title') {
                            ($this->language->exists($item->nodeValue)) ? $a_data['title'] = $this->language->get($item->nodeValue) : $a_data['title'] = $item->nodeValue;
                        } else {
                            $a_data[$item->tagName] = $item->nodeValue;
                        }
                }
                if (array_key_exists('id', $a_data)) {
                    $a_data['item_id'] = $a_data['id'];
                }
                $a_data['name'] = $i_blockNr;
                
                $this->template->setBlock('tab_' . $i, $a_data);
                
                $this->setLinks($a_links, $i_blockNr);
                
                $i_blockNr ++;
            }
            
            $i ++;
        }
        
        $s_link = '{NIV}combiner/javascript/';
        $this->template->setJavascriptLink('<script src="'.$s_link.implode(',',$this->a_jsItems).'"></script>');
        $this->template->setCssLink('<link rel="stylesheet" href="{NIV}combiner/css/'.implode(',',$this->a_cssItems).'">');
    }

    /**
     * Sets the javascript links
     *
     * @param string $s_module
     *            The module name
     * @param string $s_jsLink
     *            The JS links, seperated with a comma
     */
    private function setJS($s_module, $s_jsLink)
    {
        if (empty($s_jsLink)) {
            return;
        }
        
        $a_js = explode(',', $s_jsLink);
        if( in_array($s_module,$this->controlPanelModules->getCoreModules()) ){
            $a_items = array('{NIV}vendor/youconix/admin/modules/'.$s_module);
        }
        else {
            $a_items = array('{NIV}admin/modules/'.$s_module);
        }
        foreach ($a_js as $s_jsLink) {
        	$a_items[] = trim($s_jsLink);
        }        
        $this->a_jsItems[] = implode(';',$a_items);
        
    }

    /**
     * Sets the css links
     *
     * @param string $s_module
     *            The module name
     * @param string $s_css
     *            The CSS links, seperated with a comma
     */
    private function setCSS($s_module, $s_css)
    {
        if (empty($s_css)) {
            return;
        }
        
        $a_css = explode(',', $s_css);
        if( in_array($s_module,$this->controlPanelModules->getCoreModules()) ){
            $a_items = array('{NIV}'.'vendor/youconix/admin/modules/'.$s_module);
        }
        else {
            $a_items = array('{NIV}admin/modules/'.$s_module);
        }
        foreach ($a_css as $s_css) {
        	$a_items[] = trim($s_css);            
        }
        $this->a_cssItems[] = implode(';',$a_items);
    }

    private function setLinks($a_links, $s_module)
    {
        foreach ($a_links as $obj_link) {
            $a_data = array();
            
            foreach ($obj_link->childNodes as $item) {
                if ($item->tagName == 'title') {
                    ($this->language->exists($item->nodeValue)) ? $a_data['title'] = $this->language->get($item->nodeValue) : $a_data['title'] = $item->nodeValue;
                } else {
                    $a_data[$item->tagName] = $item->nodeValue;
                }
            }
            
            $a_data['link_title'] = $a_data['title'];
            $a_data['link_id'] = $a_data['id'];
            
            $this->template->setBlock('link_' . $s_module, $a_data);
        }
    }
}