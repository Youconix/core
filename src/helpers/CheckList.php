<?php
namespace youconix\core\helpers;

/**
 * Checkbox list widget
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
 */
class Helper_CheckList extends Helper
{

    /**
     *
     * @var \Output
     */
    protected $template;

    /**
     *
     * @var \youconix\core\helpers\HTML
     */
    protected $html;

    protected $s_callback = null;

    protected $s_name;

    protected $a_values;

    /**
     * PHP 5 constructor
     *
     * @param \youconix\core\helpers\HTML $html
     *            @parma \Output $template
     */
    public function __construct(\youconix\core\helpers\HTML $html, \Output $template)
    {
        $this->a_values = array();
        
        $this->html = $html;
        $this->template = $template;
    }

    /**
     * Adds a checkbox
     *
     * @param string $s_name
     *            checkbox name
     * @param string $s_value
     *            checkbox value
     * @param string $s_label
     *            label value
     * @param boolean $bo_checked
     *            true to set the checkbox default checked
     */
    public function addCheckbox($s_name, $s_value, $s_label = '', $bo_checked = false)
    {
        $this->a_values[$s_name] = array(
            $s_value,
            $s_label,
            $bo_checked
        );
    }

    /**
     * Sets the javascript callback
     *
     * @param string $s_callback
     *            callback
     */
    public function setCallback($s_callback)
    {
        $this->s_callback = $s_callback;
    }

    /**
     * Generates the list
     *
     * @return string list
     */
    public function generate()
    {
        $a_list = $this->generateList();
        
        $s_output = '';
        foreach ($a_list as $obj_checkbox) {
            $s_output .= $obj_checkbox->generateItem() . "\n";
        }
        
        /* Generate widget */
        $obj_out = $this->html->div();
        $obj_out->setID('checkList')->setClass('widget');
        $obj_out->setContent($s_output);
        
        return $obj_out->generateItem();
    }

    /**
     * Generates the checkboxes
     *
     * @return array checkboxes
     */
    protected function generateList()
    {
        $a_list = array();
        $a_keys = array_keys($this->a_values);
        foreach ($a_keys as $s_key) {
            $obj_checkbox = $this->html->checkbox();
            $obj_checkbox->setID($s_key);
            $obj_checkbox->setName($s_key);
            $obj_checkbox->setValue($this->a_values[$s_key][0]);
            if (! empty($this->a_values[$s_key][1]))
                $obj_checkbox->setLabel($this->a_values[$s_key][1]);
            else
                $obj_checkbox->setLabel($this->a_values[$s_key][0]);
            
            if ($this->a_values[$s_key][2])
                $obj_checkbox->setChecked();
            
            if (! is_null($this->s_callback))
                $obj_checkbox->setEvent('onclick', $this->s_callback);
            
            $a_list[] = $obj_checkbox;
        }
        
        return $a_list;
    }
}