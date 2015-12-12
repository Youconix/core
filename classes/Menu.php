<?php
namespace youconix\core\classes;

/**
 * Site menu
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
 */
class Menu implements \Menu
{

    /**
     *
     * @var \youconix\core\services\Template
     */
    protected $template;

    /**
     *
     * @var \Language
     */
    protected $language;

    /**
     *
     * @var \youconix\core\models\data\User
     */
    protected $user;

    /**
     * Starts the class menu
     *
     * @param \Output $template            
     * @param \Language $language            
     * @param \youconix\core\models\User $user          
     */
    public function __construct(\Output $template, \Language $language, \youconix\core\models\User $user)
    {
        $this->template = $template;
        $this->language = $language;
        $this->user = $user->get();
    }

    /**
     * Generates the menu
     */
    public function generateMenu()
    {
        $this->template->set('home', $this->language->get('menu/home'));
        
        if (defined('USERID')) {
            $this->template->displayPart('menuLoggedIn');
            
            $this->loggedIn();
        } else {
            $this->loggedout();
        }
    }

    /**
     * Displays the logged out items
     */
    protected function loggedout()
    {
        $this->template->set('login', $this->language->get('menu/login'));
        $this->template->set('registration', $this->language->get('menu/registration'));
    }

    /**
     * Displays the logged in items
     */
    protected function loggedIn()
    {
        $this->template->set('logout', $this->language->get('menu/logout'));
        
        if ($this->user->isAdmin(GROUP_ADMIN)) {
            $this->template->displayPart('menuAdmin');
            
            $this->template->set('adminPanel', $this->language->get('system/menu/adminPanel'));
        }
    }
}