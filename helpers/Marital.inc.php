<?php

/**
 * Marital list widget
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author    Rachelle Scheijen
 * @since     1.0
 *
 * Miniature-happiness is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Miniature-happiness is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Miniature-happiness.  If not, see <http://www.gnu.org/licenses/>.
 */
class Helper_Marital extends Helper
{

    private $a_items;

    /**
     * Creates the marital helper
     */
    public function __construct()
    {
        $service_Language = Memory::services('Language');
        
        $this->a_items = array(
            'married' => $service_Language->get('marital/married'),
            'registeredPartner' => $service_Language->get('marital/registeredPartner'),
            'divorced' => $service_Language->get('marital/divorced'),
            'unknown' => $service_Language->get('marital/unknown'),
            'single' => $service_Language->get('marital/single'),
            'livingTogetherContract' => $service_Language->get('marital/livingTogetherContract'),
            'livingTogether' => $service_Language->get('marital/livingTogether'),
            'widow' => $service_Language->get('marital/widow')
        );
    }

    /**
     * Checks if the key is valid
     *
     * @param string $s_key
     *            key
     *            @returb bool	True if the key is valid
     */
    public function isValid($s_key)
    {
        return (array_key_exists($s_key, $this->a_items));
    }

    /**
     * Returns the item value
     *
     * @param string $s_key
     *            key
     * @return string value
     * @throws \OutOfBoundsException the key is invalid
     */
    public function getItem($s_key)
    {
        if (! $this->is_valid($s_key)) {
            throw new \OutOfBoundsException("Invalid key " . $s_key . ". Only 'married','registeredPartner','divorced','unknown','single','livingTogetherContract','livingTogether' and 'widow' are allowed.");
        }
        
        return $this->a_items[$s_key];
    }

    /**
     * Returns the martial items
     *
     * @return array items
     */
    public function getItems()
    {
        return $this->a_items;
    }

    /**
     * Generates the selection list
     *
     * @param string $s_field
     *            list name
     * @param string $s_id
     *            list id
     * @param string $s_default
     *            default value, optional
     * @return string list
     */
    public function getList($s_field, $s_id, $s_default = '')
    {
        $obj_Select = Memory::helpers('HTML')->select($s_field);
        $obj_Select->setID($s_id);
        
        $a_items = $this->getItems();
        foreach ($a_items as $s_key => $s_value) {
            ($s_key == $s_default) ? $bo_selected = true : $bo_selected = false;
            
            $obj_Select->setOption($s_value, $bo_selected, $s_key);
        }
        
        return $obj_Select->generateItem();
    }
}