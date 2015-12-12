<?php

class Helper_Gender extends Helper
{

    private $a_genders;

    public function __construct()
    {
        $service_language = Memory::services('Language');
        $this->a_genders = array(
            'M' => $service_language->get('gender/male'),
            'F' => $service_language->get('gender/female'),
            'O' => $service_language->get('gender/other')
        );
    }

    /**
     * Returns the gender's text.
     *
     * @param String $s_code            
     * @throws \InvalidArgumentException
     */
    public function getGender($s_code)
    {
        if (! array_key_exists($s_code, $this->a_genders)) {
            throw new \InvalidArgumentException("Illegal gender: " . $s_code . ". Only M, F and O are valid.");
        }
        return $this->a_genders[$s_code];
    }

    public function getList($s_field, $s_id, $s_gender = '')
    {
        $obj_Select = Memory::helpers('HTML')->select($s_field);
        $obj_Select->setID($s_id);
        
        foreach ($this->a_genders as $s_key => $s_value) {
            ($s_key == $s_gender) ? $bo_selected = true : $bo_selected = false;
            
            $obj_Select->setOption($s_value, $bo_selected, $s_key);
        }
        
        return $obj_Select->generateItem();
    }
}