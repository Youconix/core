<?php

namespace youconix\Core\Classes;

/**
 * Stack class.
 * This collection works with the principal first in, last out
 *
 * @author Rachelle Scheijen
 * @since 1.0
 * @deprecated
 *
 * @see http://php.net/manual/en/class.splstack.php
 */
class Stack
{

  private $content;

  private $counter;

  /**
   * Creates a new stack
   *
   * @param $content
   */
  public function __construct($content = [])
  {
    if (!\youconix\core\Memory::isTesting()) {
      trigger_error("This class has been deprecated in favour of SplStack.", E_USER_DEPRECATED);
    }
    $this->clear();

    $this->addArray($content);
  }

  /**
   * Merges the given stack with this one
   *
   * @param Stack $stack
   * @throws \Exception $stack if not a Stack
   */
  public function addStack($stack)
  {
    if (!($stack instanceof Stack)) {
      throw new StackException("Can only add Stacks");
    }

    while (!$stack->isEmpty()) {
      $this->push($stack->pop());
    }
  }

  /**
   * Adds the array to the stack
   *
   * @param array $content
   */
  public function addArray(array $content)
  {
    foreach ($content as $item) {
      $this->push($item);
    }
  }

  /**
   * Pushes the item at the end of the stack
   *
   * @param mixed $item
   */
  public function push($item)
  {
    $this->content[] = $item;
    $this->counter++;
  }

  /**
   * Retrieves and removes the end of this stack
   *
   * @return mixed The last element of the stack.
   * @throws StackException the stack is empty
   */
  public function pop()
  {
    if ($this->isEmpty()) {
      throw new StackException("Can not pop from empty stack");
    }

    $s_content = $this->content[$this->counter];
    $this->content[$this->counter] = null;
    $this->counter--;

    return $s_content;
  }

  /**
   * Retrieves end of this stack without removing it
   *
   * @return mixed The last element of the stack.
   * @throws StackException the stack is empty
   */
  public function peek()
  {
    if ($this->isEmpty()) {
      throw new StackException("Can not peek from empty stack");
    }

    return $this->content[$this->counter];
  }

  /**
   * Searches if the stack contains the given item
   *
   * @param Object $search
   *            item
   * @return Boolean if the queue contains the item
   */
  public function search($search)
  {
    for ($i = 0; $i <= $this->counter; $i++) {
      if (is_object($this->content[$i]) && ($this->content[$i] instanceof String)) {
        if ($this->content[$i]->equals($search)) {
          return true;
        }
      }
      if ($this->content[$i] == $search) {
        return true;
      }
    }

    return false;
  }

  /**
   * Checks if the stack is empty
   *
   * @return boolean if the stack is empty
   */
  public function isEmpty()
  {
    return ($this->counter == -1);
  }

  /**
   * Clears the stack
   */
  public function clear()
  {
    $this->content = [];
    $this->counter = -1;
  }
}

class StackException extends \Exception
{
}