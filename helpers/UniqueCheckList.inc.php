<?php
/** 
 * Checkbox group list widget
 * All checkboxes in a group have the same name                           
 *                                                                              
 * This file is part of Miniature-happiness                                    
 *                                                                              
 * @copyright Youconix                                
 * @author    Rachelle Scheijen                                                
 * @since     1.0        
 * @see		   core/helpers/CheckList.inc.php
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
require_once (NIV . 'include/helpers/CheckList.inc.php');

class Helper_UniqueCheckList extends Helper_CheckList
{

    private $s_listName;

    /**
     * PHP 5 constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->s_name = 'uniqueCheckListName';
        $this->s_listName = $this->s_name;
    }

    /**
     * Adds a checkbox
     *
     * @param string $s_value
     *            checkbox value
     * @param string $s_label
     *            label value
     * @param boolean $bo_checked
     *            true to set the checkbox default checked
     */
    public function addCheckbox($s_value, $s_label = '', $bo_checked = false)
    {
        $this->a_values[] = array(
            $s_value,
            $s_label,
            $bo_checked
        );
    }

    /**
     * Sets the name of the checkboxes
     *
     * @param string $s_name
     *            name
     */
    public function setListName($s_name)
    {
        $this->s_listName = $s_name;
    }

    /**
     * Generates the checkboxes
     *
     * @return array checkboxes
     */
    protected function generateList()
    {
        $a_list = array();
        
        $i = 1;
        foreach ($this->a_values as $a_checkbox) {
            $obj_checkbox = $this->helper_HTML->checkbox();
            $obj_checkbox->setID($this->s_listName . '_' . $i);
            $obj_checkbox->setValue($a_checkbox[0]);
            $obj_checkbox->setName($this->s_listName);
            if (! empty($a_checkbox[1]))
                $obj_checkbox->setLabel($a_checkbox[1]);
            else
                $obj_checkbox->setLabel($a_checkbox[0]);
            
            if ($a_checkbox[2])
                $obj_checkbox->setChecked();
            
            if (! is_null($this->s_callback))
                $obj_checkbox->setEvent('onclick', $this->s_callback);
            
            $a_list[] = $obj_checkbox;
            $i ++;
        }
        
        return $a_list;
    }
}