<?php
namespace youconix\core\helpers;

/**
 * Radio list widget
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
 */
class RadioList extends \youconix\core\helpers\Helper
{

    /**
     *
     * @var \youconix\core\helpers\HTML
     */
    protected $HTML;

    /**
     *
     * @var \Ouput
     */
    protected $template;

    protected $s_callback = 'null';

    protected $a_values;

    /**
     * PHP 5 constructor
     *
     * @param \Ouput $template            
     * @param \youconix\core\helpers\HTML $HTML            
     */
    public function __construct(\Ouput $template, \youconix\core\helpers\HTML $HTML)
    {
        $this->a_values = array();
        
        $this->HTML = $HTML;
        $this->template = $template;
    }

    /**
     * Adds a radio button
     *
     * @param string $s_name
     *            name
     * @param string $s_value
     *            value
     * @param string $s_label
     *            label value
     * @param boolean $bo_checked
     *            true to set the radio button default checked
     */
    public function addRadio($s_name, $s_value, $s_label = '', $bo_checked = false)
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
        foreach ($a_list as $obj_radio) {
            $s_output .= $obj_radio->generateItem() . "\n";
        }
        
        /* Generate widget */
        $obj_out = $this->HTML->div();
        $obj_out->setID('radioList')->setClass('widget');
        $obj_out->setContent($s_output);
        
        return $obj_out->generateItem();
    }

    /**
     * Generates the radio buttons
     *
     * @return array radio buttons
     */
    protected function generateList()
    {
        $a_list = array();
        $a_keys = array_keys($this->a_values);
        foreach ($a_keys as $s_key) {
            $obj_radio = $this->HTML->radio();
            $obj_radio->setID($s_key);
            $obj_radio->setName($s_key);
            $obj_radio->setValue($this->a_values[$s_key][0]);
            $obj_radio->setLabel($this->a_values[$s_key][1]);
            if ($this->a_values[$s_key][2])
                $obj_radio->setChecked();
            
            if (! is_null($this->s_callback))
                $obj_radio->setEvent('onclick', $this->s_callback);
            
            $a_list[] = $obj_radio;
        }
        
        return $a_list;
    }
}