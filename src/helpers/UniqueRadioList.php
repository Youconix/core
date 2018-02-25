<?php
namespace youconix\core\helpers;

/**
 * Radio group list widget
 * All radio buttons in a group have the same name
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
 * @see Core/helpers/RadioList.inc.php
 */
class UniqueRadioList extends \youconix\core\helpers\RadioList
{

    protected $s_listName;

    /**
     * PHP 5 constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->s_name = 'uniqueRadioListName';
        $this->s_listName = $this->s_name;
    }

    /**
     * Adds a radio button
     *
     * @param string $s_value
     *            value
     * @param string $s_label
     *            label value
     * @param boolean $bo_checked
     *            true to set the radio button default checked
     */
    public function addRadio($s_value, $s_label = '', $bo_checked = false)
    {
        $this->a_values[] = array(
            $s_value,
            $s_label,
            $bo_checked
        );
    }

    /**
     * Sets the name of the radio buttons
     *
     * @param string $s_name
     *            name
     */
    public function setListName($s_name)
    {
        $this->s_listName = $s_name;
    }

    /**
     * Generates the radio buttons
     *
     * @return array radio buttons
     */
    protected function generateList()
    {
        $a_list = array();
        
        $i = 1;
        foreach ($this->a_values as $a_radio) {
            $obj_radio = $this->helper_HTML->radio();
            $obj_radio->setID($this->s_name . '_' . $i);
            $obj_radio->setName($this->s_name);
            $obj_radio->setValue($a_radio[0]);
            $obj_radio->setLabel($a_radio[1]);
            if ($a_radio[2])
                $obj_radio->setChecked();
            
            if (! is_null($this->s_callback))
                $obj_radio->setEvent('onclick', $this->s_callback);
            
            $a_list[] = $obj_radio;
            $i ++;
        }
        
        return $a_list;
    }
}