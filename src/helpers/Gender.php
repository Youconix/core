<?php
namespace youconix\core\helpers;

/**
 * Gender list generator
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
 */

class Gender extends \youconix\core\helpers\Helper
{
    /**
     * 
     * @var \youconix\core\helpers\HTML
     */
    protected $html;
    protected $a_genders;

    /**
     * Constructor
     * 
     * @param \Language $language
     * @param \youconix\core\helpers\HTML $html
     */
    public function __construct(\Language $language,\youconix\core\helpers\HTML $html)
    {
        $this->html = $html;
        
        $this->a_genders = array(
            'M' => $language->get('system/gender/male'),
            'F' => $language->get('system/gender/female'),
            'O' => $language->get('system/gender/other')
        );
    }

    /**
     * Returns the gender's text.
     *
     * @param string $s_code            
     * @throws \InvalidArgumentException
     */
    public function getGender($s_code)
    {
        if (! array_key_exists($s_code, $this->a_genders)) {
            throw new \InvalidArgumentException("Illegal gender: " . $s_code . ". Only M, F and O are valid.");
        }
        return $this->a_genders[$s_code];
    }

    /**
     * Generates the list
     * 
     * @param string $s_name
     * @param unknown $s_id
     * @param string $s_gender
     * @return string
     */
    public function getList($s_name, $s_id, $s_gender = '')
    {
        $select = $this->html->select($s_name);
        $select->setID($s_id);
        
        foreach ($this->a_genders as $s_key => $s_value) {
            ($s_key == $s_gender) ? $bo_selected = true : $bo_selected = false;
            
            $select->setOption($s_value, $bo_selected, $s_key);
        }
        
        return $select->generateItem();
    }
}