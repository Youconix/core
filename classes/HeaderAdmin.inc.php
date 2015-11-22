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
 * Site header
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
 */
class HeaderAdmin extends \core\classes\Header
{
    /**
     * Starts the class header
     */
    public function __construct(\Output $template, \Language $language, \core\models\User $model_User, \Config $model_Config)
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