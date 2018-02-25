<?php

namespace youconix\core\classes;

/**
 * String class.
 * Contains all the possible string operations
 *
 * @author Rachelle Scheijen
 * @since 1.0
 */
class String
{

  private $content = '';

  private $size = 0;

  /**
   * Generates a new String
   *
   * @param string $value
   */
  public function __construct($value = '')
  {
    $this->set($value);
  }

  /**
   * Sets the value, overwrites any existing value
   *
   * @param string $value
   */
  public function set($value)
  {
    $this->content = $value;
    $this->size = strlen($value);
  }

  /**
   * Appends the given value to the existing value
   *
   * @param string $value
   */
  public function append($value)
  {
    $this->content .= $value;
    $this->size += strlen($value);
  }

  /**
   * Returns the length
   *
   * @return int length
   */
  public function length()
  {
    return $this->size;
  }

  /**
   * Returns the value
   *
   * @return string value
   */
  public function value()
  {
    return $this->content;
  }

  /**
   * Checks if the value starts with the given text
   *
   * @param string $text
   * @return boolean if the value starts with the given text
   */
  public function startsWith($text)
  {
    if (substr($this->content, 0, strlen($text)) == $text)
      return true;

    return false;
  }

  /**
   * Checks if the value ends with the given text
   *
   * @param string $text
   * @return boolean if the value ends with the given text
   */
  public function endsWith($text)
  {
    if (substr($this->content, (strlen($text) * -1)) == $text)
      return true;

    return false;
  }

  /**
   * Checks if the value contains the given text
   *
   * @param string $text
   * @param boolean $caseSensitive
   * @return boolean
   */
  public function contains($text, $caseSensitive = true)
  {
    if ($caseSensitive) {
      $pos = stripos($this->content, $text);
    } else {
      $pos = strpos($this->content, $text);
    }

    if ($pos === false)
      return false;

    return true;
  }

  /**
   * Checks if the value is equal to the given text
   *
   * @param string $text
   * @return boolean if the text is equal
   */
  public function equals($text)
  {
    return ($this->content == $text);
  }

  /**
   * Checks if the value is equal to the given text with ignoring the case
   *
   * @param string $text
   * @return boolean if the text is equal
   */
  public function equalsIgnoreCase($text)
  {
    $text = strToLower($text);
    $s_check = strToLower($this->content);

    return ($s_check == $text);
  }

  /**
   * Returns the start position of the given text
   *
   * @param string $search
   * @return  int  The start position or -1 when the text is not found
   */
  public function indexOf($search)
  {
    $pos = stripos($this->content, $search);
    if ($pos === false) {
      $pos = -1;
    }

    return $pos;
  }

  /**
   * Checks if the string is empty
   *
   * @return boolean if the string is empty
   */
  public function isEmpty()
  {
    return ($this->size == 0);
  }

  /**
   * Removes the spaces at the begin and end
   */
  public function trim()
  {
    return trim($this->content);
  }

  /**
   * Replaces the given search with the given text if the value contains the given search
   *
   * @param string $search
   * @param string $replace
   */
  public function replace($search, $replace)
  {
    $this->set(str_replace($search, $replace, $this->content));
  }

  /**
   * Returns the substring from the current value
   *
   * @param int $start
   * @param int $end
   * @return string substring
   */
  public function substring($start, $end = -1)
  {
    if ($end == -1) {
      return substr($this->content, $start);
    } else {
      return substr($this->content, $start, $end);
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