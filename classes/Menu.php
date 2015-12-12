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
     * @var \core\services\Template
     */
    protected $template;

    /**
     *
     * @var \Language
     */
    protected $language;

    /**
     *
     * @var \core\models\data\User
     */
    protected $user;

    /**
     * Starts the class menu
     *
     * @param \Output $template            
     * @param \Language $language            
     * @param \core\models\User $user          
     */
    public function __construct(\Output $template, \Language $language, \core\models\User $user)
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