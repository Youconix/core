<?php
namespace youconix\core\helpers;

/**
 * Countries list widget
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
 */
class Countries extends Helper
{
    /**
     * 
     * @var \youconix\core\helpers\HTML
     */
    protected $html;
    protected $a_countries = array();

    /**
     * Creates the country helper
     *
     * @param \Builder $builder            
     * @param \Ĺanguage $language
     * @param \youconix\core\helpers\HTML $html            
     */
    public function __construct(\Builder $builder, \Language $language,\youconix\core\helpers\HTML $html)
    {
        $this->html = $html;
        
        $s_language = $language->getLanguage();
        $builder->select("countries", "id," . $s_language . ' AS country');
        $database = $builder->getResult();
        if ($database->num_rows() > 0) {
            $a_data = $database->fetch_assoc_key('country');
            ksort($a_data, SORT_STRING);
            
            foreach ($a_data as $a_item) {
                $this->a_countries[$a_item['id']] = $a_item;
            }
        }
    }

    /**
     * Returns the country
     *
     * @param int $i_id
     *            country ID
     * @return array country
     * @throws OutOfBoundsException the ID does not exist
     */
    public function getItem($i_id)
    {
        if (! array_key_exists($i_id, $this->a_countries)) {
            throw new \OutOfBoundsException("Call to unknown country with id " . $i_id . '.');
        }
        
        return $this->a_countries[$i_id];
    }

    /**
     * Returns the countries sorted on name
     *
     * @return array countries
     */
    public function getItems()
    {
        return $this->a_countries;
    }

    /**
     * Generates the selection list
     *
     * @param string $s_field
     *            list name
     * @param string $s_id
     *            list id
     * @param int $i_default
     *            default value, optional
     * @return string list
     */
    public function getList($s_field, $s_id, $i_default = -1)
    {
        $list = $this->html->select($s_field);
        $list->setId($s_id);
                
        foreach ($this->a_countries as $a_country) {
            ($a_country['id'] == $i_default) ? $bo_selected = true : $bo_selected = false;
        
            $option = $list->setOption($a_country['country'], $bo_selected,$a_country['id']);
        }
        
        return $list->generateItem();
    }
}