<?php
namespace youconix\core\helpers;

/**
 * Marital list widget
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
 */
class Marital extends \youconix\core\helpers\Helper
{

    protected $a_items;

    /**
     * Creates the marital helper
     *
     * @param \Language $language            
     */
    public function __construct(\Language $language)
    {
        $this->a_items = array(
            'married' => $language->get('system/marital/married'),
            'registeredPartner' => $language->get('system/marital/registeredPartner'),
            'divorced' => $language->get('system/marital/divorced'),
            'unknown' => $language->get('system/marital/unknown'),
            'single' => $language->get('system/marital/single'),
            'livingTogetherContract' => $language->get('system/marital/livingTogetherContract'),
            'livingTogether' => $language->get('system/marital/livingTogether'),
            'widow' => $language->get('system/marital/widow')
        );
    }

    /**
     * Checks if the key is valid
     *
     * @param string $s_key
     *            key
     * @return bool True if the key is valid
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