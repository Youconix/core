<?php
namespace core\services;

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
 * Settings handler.
 * This class contains all the framework settings.
 * The settings file is stored in de settings directory in de data dir (default admin/data)
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @version 1.0
 * @see core/services/Xml.inc.php
 * @since 1.0
 */
class Settings extends \core\services\Xml implements \Settings
{

    private $s_settingsDir;
    
    /**
     * PHP 5 constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->s_settingsDir = DATA_DIR . 'settings';
        
        if (file_exists($this->s_settingsDir . '/settings.xml')) {
            $this->load($this->s_settingsDir . '/settings.xml');
        } else {
            $s_base = \core\Memory::detectBase();
            
            \core\Memory::redirect($s_base . '/install/');
            exit();
        }
        
        $this->s_startTag = 'settings';
    }

    /**
     * Returns if the object schould be treated as singleton
     *
     * @return boolean True if the object is a singleton
     */
    public static function isSingleton()
    {
        return true;
    }

    /**
     * Saves the settings file
     */
    public function save($s_file = '')
    {
        parent::save($this->s_settingsDir . '/settings.xml');
    }
}