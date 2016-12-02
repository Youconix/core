<?php
namespace youconix\core\classes;

/**
 * Admin site header
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
 */
class HeaderAdmin extends \youconix\core\classes\Header
{
    /**
     * Generates the header
     * 
     * @param \Output $template
     */
    public function createHeader(\Output $template)
    {
      $this->template = $template;
        $obj_User = $this->auth->getUser();
        
        $this->template->set('logout', $this->language->get('system/admin/menu/logout'));
        $this->template->set('close', $this->language->get('system/admin/menu/close'));
        $this->template->set('adminMenuLink', $this->language->get('system/admin/menu/adminMenuLink'));
        $this->template->set('loginHeader', $this->language->insertPath('system/admin/menu/loginHeader', 'name', $obj_User->getUsername()));
        
        $this->displayLanguageFlags();
    }
}