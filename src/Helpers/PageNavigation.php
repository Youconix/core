<?php
namespace youconix\core\helpers;

/**
 * Helper for generating page navigation
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
 */
class PageNavigation extends \youconix\core\helpers\Helper
{

    protected $s_class;

    protected $i_itemsProPage;

    protected $i_items;

    protected $i_page;

    protected $s_url;

    /**
     * Creates a new page navigation
     * The current page is transmitted via the GET variable page
     *
     * Default settings :
     * class pageNav
     * itemsProPage 25
     * items 0
     * page 1
     * url $_SERVER['PHP_SELF']
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
     * @param string $s_class
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
     * @param string $s_url
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
     * @return string The code
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