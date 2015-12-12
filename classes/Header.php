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
class Header implements \Header
{

    /**
     *
     * @var \Output
     */
    protected $template;

    /**
     *
     * @var \Language
     */
    protected $language;

    /**
     *
     * @var \core\models\User
     */
    protected $user;

    /**
     *
     * @var \Config
     */
    protected $config;

    /**
     * Starts the class header
     * 
     * @param \Output $template
     * @param \Language $language
     * @param \core\models\User $user
     * @param \Config $config
     */
    public function __construct(\Output $template, \Language $language, \core\models\User $user, \Config $config)
    {
        $this->template = $template;
        $this->language = $language;
        $this->user = $user;
        $this->config = $config;
    }

    /**
     * Generates the header
     */
    public function createHeader()
    {
        $this->displayLanguageFlags();
        
        $obj_User = $this->user->get();
        if (is_null($obj_User->getID())) {
            return;
        }
        
        if ($obj_User->isAdmin(GROUP_SITE)) {
            $s_welcome = $this->language->get('system/header/adminWelcome');
        } else {
            $s_welcome = $this->language->get('system/header/userWelcome');
        }
        
        $this->template->set('welcomeHeader', '<a href="{NIV}profile/view/details/id=' . $obj_User->getID() . '" style="color:' . $obj_User->getColor() . '">' . $s_welcome . ' ' . $obj_User->getUsername() . '</a>');
    }

    /**
     * Displays the language change flags
     */
    protected function displayLanguageFlags()
    {
        $a_languages = $this->config->getLanguages();
        $a_languagesCodes = $this->language->getLanguageCodes();
        
        foreach ($a_languages as $s_code) {
            $s_language = (array_key_exists($s_code, $a_languagesCodes)) ? $a_languagesCodes[$s_code] : $s_code;
            
            $this->template->setBlock('headerLanguage', array(
                'code' => $s_code,
                'language' => $s_language
            ));
        }
    }
}