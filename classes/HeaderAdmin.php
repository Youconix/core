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
     * Starts the class header
     */
    public function __construct(\Output $template, \Language $language, \youconix\core\models\User $model_User, \Config $model_Config)
    {
        parent::__construct($template, $language, $model_User, $model_Config);
    }

    /**
     * Generates the header
     */
    public function createHeader()
    {
        $obj_User = $this->user->get();
        
        $this->template->set('logout', $this->language->get('system/admin/menu/logout'));
        $this->template->set('close', $this->language->get('system/admin/menu/close'));
        $this->template->set('adminMenuLink', $this->language->get('system/admin/menu/adminMenuLink'));
        $this->template->set('loginHeader', $this->language->insertPath('system/admin/menu/loginHeader', 'name', $obj_User->getUsername()));
        
        $this->displayLanguageFlags();
    }
}