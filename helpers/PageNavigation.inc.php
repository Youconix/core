<?php
namespace core\helpers;

/**
 * Helper for generating page navigation
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
class PageNavigation extends Helper
{

    private $s_class;

    private $i_itemsProPage;

    private $i_items;

    private $i_page;

    private $s_url;

    /**
     * Creates a new page navigation
     * The current page is transmitted via the GET variable page
     *
     * Default settings :
     * class					pageNav
     * itemsProPage	25
     * items					0
     * page					1
     * url						$_SERVER['PHP_SELF']
     */
    public function __construct()
    {
        $this->s_class = 'pageNav';
        $this->i_itemsProPage = 25;
        $this->i_items = 0;
        $this->i_page = 1;
        $this->s_url = $_SERVER['PHP_SELF'];
    }

    /**
     * Sets the class name
     *
     * @param String $s_class
     *            class name
     */
    public function setClass($s_class)
    {
        $this->s_class = $s_class;
        
        return $this;
    }

    /**
     * Sets the amount of items pro page
     *
     * @param int $i_items
     *            amount of items
     */
    public function setItemsProPage($i_items)
    {
        $this->i_itemsProPage = $i_items;
        
        return $this;
    }

    /**
     * Sets the total amount of items
     *
     * @param int $i_items
     *            amount of items
     */
    public function setAmount($i_items)
    {
        $this->i_items = $i_items;
        
        return $this;
    }

    /**
     * Sets the page url
     *
     * @param String $s_url
     *            url
     */
    public function setUrl($s_url)
    {
        $this->s_url = $s_url;
        
        return $this;
    }

    /**
     * Sets the current page number
     *
     * @param int $i_page
     *            page number
     */
    public function setPage($i_page)
    {
        $this->i_page = $i_page;
        
        return $this;
    }

    /**
     * Generates the navigation code
     *
     * @return String The code
     */
    public function generateCode()
    {
        if ($this->i_items < $this->i_itemsProPage)
            return '';
        
        $bo_javascript = false;
        if (strpos($this->s_url, 'javascript:') !== false) {
            $bo_javascript = true;
        } else 
            if (strpos($this->s_url, '?') === false) {
                $this->s_url .= '?';
            } else {
                $this->s_url .= '&amp;';
            }
        
        $s_code = '<ul class="' . $this->s_class . '">';
        
        if ($this->i_page != 1) {
            if ($bo_javascript) {
                $s_code .= '<li><a href="' . str_replace('{page}', ($this->i_page - 1), $this->s_url) . '">&lt;&lt;</a></li>
	      	';
            } else {
                $s_code .= '<li><a href="' . $this->s_url . 'page=' . ($this->i_page - 1) . '">&lt;&lt;</a></li>
      		';
            }
        }
        
        $i_page = 1;
        $i_pos = 0;
        while ($i_pos < $this->i_items) {
            ($i_page == $this->i_page) ? $s_selected = ' class="bold"' : $s_selected = '';
            
            if ($bo_javascript) {
                $s_code .= '<li><a href="' . str_replace('{page}', $i_page, $this->s_url) . '"' . $s_selected . '>' . $i_page . '</a></li>
	      	';
            } else {
                $s_code .= '<li><a href="' . $this->s_url . 'page=' . $i_page . '"' . $s_selected . '>' . $i_page . '</a></li>
      		';
            }
            
            $i_page ++;
            $i_pos += $this->i_itemsProPage;
        }
        
        if ($this->i_page != $i_page) {
            if ($bo_javascript) {
                $s_code .= '<li><a href="' . str_replace('{page}', ($this->i_page + 1), $this->s_url) . '">&gt;&gt;</a></li>
	      	';
            } else {
                $s_code .= '<li><a href="' . $this->s_url . 'page=' . ($this->i_page + 1) . '">&gt;&gt;</a></li>
      		';
            }
        }
        
        $s_code .= '</ul>';
        
        return $s_code;
    }
}