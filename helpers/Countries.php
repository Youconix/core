<?php
namespace core\helpers;

/**
 * Countries list widget
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
 *       
 *        Miniature-happiness is free software: you can redistribute it and/or modify
 *        it under the terms of the GNU Lesser General Public License as published by
 *        the Free Software Foundation, either version 3 of the License, or
 *        (at your option) any later version.
 *       
 *        Miniature-happiness is distributed in the hope that it will be useful,
 *        but WITHOUT ANY WARRANTY; without even the implied warranty of
 *        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *        GNU General Public License for more details.
 *       
 *        You should have received a copy of the GNU Lesser General Public License
 *        along with Miniature-happiness. If not, see <http://www.gnu.org/licenses/>.
 */
class Countries extends Helper
{

    private $a_countries = array();

    /**
     * Creates the country helper
     *
     * @param \Builder $service_Builder
     *            The query builder
     * @param \Ĺanguage $service_Language
     *            The language service
     *            
     * @todo	Reimplement constructor
     */
    public function __construct(\Builder $service_Builder, \Language $service_Language)
    {
        /*
         * $service_Builder = $service_Builder->createBuilder();
         * $s_language = $service_Language->getLanguage();
         *
         * $service_Builder->select("countries", "id," . $s_language . ' AS country');
         * $service_Database = $service_Builder->getResult();
         * if( $service_Database->num_rows() > 0 ){
         * $a_data = $service_Database->fetch_assoc_key('country');
         * ksort($a_data, SORT_STRING);
         *
         * foreach( $a_data AS $a_item ){
         * $this->a_countries[ $a_item[ 'id' ] ] = $a_item;
         * }
         * }
         */
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
        $s_list = '<select id="' . $s_id . '" name="' . $s_field . '">' . "\n";
        
        foreach ($this->a_countries as $a_country) {
            ($a_country['id'] == $i_default) ? $s_selected = ' selected="selected"' : $s_selected = '';
            
            $s_list .= '<option value="' . $a_country['id'] . '"' . $s_selected . '>' . $a_country['country'] . "</option>\n";
        }
        
        $s_list .= "</select>\n";
        return $s_list;
    }
}