<?php
namespace youconix\core\classes;

/**
 * String class.
 * Contains all the posible string operations
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
 */
class String
{

    private $s_content = '';

    private $i_size = 0;

    /**
     * Generates a new String
     *
     * @param string $s_value
     *            value, optional
     */
    public function __construct($s_value = '')
    {
        $this->set($s_value);
    }

    /**
     * Sets the value, overwrites any existing value
     *
     * @param string $s_value
     *            value
     */
    public function set($s_value)
    {
        $this->s_content = $s_value;
        $this->i_size = strlen($s_value);
    }

    /**
     * Appends the given value to the existing value
     *
     * @param string $s_value
     *            value
     */
    public function append($s_value)
    {
        $this->s_content .= $s_value;
        $this->i_size += strlen($s_value);
    }

    /**
     * Returns the length
     *
     * @return int length
     */
    public function length()
    {
        return $this->i_size;
    }

    /**
     * Returns the value
     *
     * @return string value
     */
    public function value()
    {
        return $this->s_content;
    }

    /**
     * Checks if the value starts with the given text
     *
     * @param string $s_text
     *            text to search on
     * @return boolean if the value starts with the given text
     */
    public function startsWith($s_text)
    {
        if (substr($this->s_content, 0, strlen($s_text)) == $s_text)
            return true;
        
        return false;
    }

    /**
     * Checks if the value ends with the given text
     *
     * @param string $s_text
     *            text to search on
     * @return boolean if the value ends with the given text
     */
    public function endsWith($s_text)
    {
        if (substr($this->s_content, (strlen($s_text) * - 1)) == $s_text)
            return true;
        
        return false;
    }

    /**
     * Checks if the value contains the given text
     *
     * @param string $s_text
     *            text to search on
     * @param boolean $bo_caseSensitive
     *            false to search case insensitive
     */
    public function contains($s_text, $bo_caseSensitive = true)
    {
        if ($bo_caseSensitive) {
            $i_pos = stripos($this->s_content, $s_text);
        } else {
            $i_pos = strpos($this->s_content, $s_text);
        }
        
        if ($i_pos === false)
            return false;
        
        return true;
    }

    /**
     * Checks if the value is equal to the given text
     *
     * @param string $s_text
     *            text to check on
     * @return boolean if the text is equal
     */
    public function equals($s_text)
    {
        return ($this->s_content == $s_text);
    }

    /**
     * Checks if the value is equal to the given text with ignoring the case
     *
     * @param string $s_text
     *            text to check on
     * @return boolean if the text is equal
     */
    public function equalsIgnoreCase($s_text)
    {
        $s_text = strToLower($s_text);
        $s_check = strToLower($this->s_content);
        
        return ($s_check == $s_text);
    }

    /**
     * Returns the start position of the given text
     *
     * @param string $s_search
     *            text to search on
     * @param
     *            int	The start position or -1 when the text is not found
     */
    public function indexOf($s_search)
    {
        $i_pos = stripos($this->s_content, $s_search);
        if ($i_pos === false)
            $i_pos = - 1;
        
        return $i_pos;
    }

    /**
     * Checks if the string is empty
     *
     * @return boolean if the string is empty
     */
    public function isEmpty()
    {
        return ($this->i_size == 0);
    }

    /**
     * Removes the spaces at the begin and end
     */
    public function trim()
    {
        return trim($this->s_content);
    }

    /**
     * Replaces the given search with the given text if the value contains the given search
     *
     * @param string $s_search
     *            text to search on
     * @param string $s_replace
     *            replacement
     */
    public function replace($s_search, $s_replace)
    {
        $this->set(str_replace($s_search, $s_replace, $this->s_content));
    }

    /**
     * Returns the substring from the current value
     *
     * @param int $i_start
     *            start position
     * @param int $i_end
     *            end position
     * @return string substring
     */
    public function substring($i_start, $i_end = -1)
    {
        if ($i_end == - 1) {
            return substr($this->s_content, $i_start);
        } else {
            return substr($this->s_content, $i_start, $i_end);
        }
    }

    /**
     * Clones the String object
     *
     * @return String clone
     */
    public function copy()
    {
        return clone $this;
    }
}