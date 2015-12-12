<?php
namespace youconix\core\helpers;

/**
 * Checkbox group list widget
 * All checkboxes in a group have the same name
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
 * @see core/helpers/CheckList.inc.php
 */
class UniqueCheckList extends \youconix\core\helpers\CheckList
{

    protected $s_listName;

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