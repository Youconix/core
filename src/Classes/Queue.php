<?php

namespace youconix\Core\Classes;

/**
 * Queue class.
 * This collection works with the principal first in, first out
 *
 * @author Rachelle Scheijen
 * @since 1.0
 * @deprecated
 *
 * @see http://php.net/manual/en/class.splqueue.php
 */
class Queue
{

  private $content;

  private $start;

  private $counter;

  /**
   * Creates a new queue
   *
   * @param $content
   */
  public function __construct($content = [])
  {
    if (!\youconix\core\Memory::isTesting()) {
      trigger_error("This class has been deprecated in favour of SplQueue.", E_USER_DEPRECATED);
    }
    $this->clear();

    $this->addArray($content);
  }

  /**
   * Merges the given queue with this one
   *
   * @param Queue $queue
   * @throws \Exception $queue if not a Queue
   */
  public function addQueue($queue)
  {
    if (!($queue instanceof Queue)) {
      throw new \Exception("Can only add Queues");
    }

    while (!$queue->isEmpty()) {
      $this->push($queue->pop());
    }
  }

  /**
   * Adds the array to the queue
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
   * Pushes the item at the end of the queue
   *
   * @param mixed $item
   */
  public function push($item)
  {
    $this->content[] = $item;
    $this->counter++;
  }

  /**
   * Retrieves and removes the head of this queue, or null if this queue is empty.
   *
   *
   * @return mixed The first element of the queue.
   */
  public function pop()
  {
    if ($this->isEmpty()) {
      return null;
    }

    $s_content = $this->content[$this->start];
    $this->content[$this->start] = null;
    $this->start++;

    return $s_content;
  }

  /**
   * Retrieves the head of this queue, or null if this queue is empty.
   *
   *
   * @return mixed The first element of the queue.
   */
  public function peek()
  {
    if ($this->isEmpty()) {
      return null;
    }

    return $this->content[$this->start];
  }

  /**
   * Searches if the queue contains the given item
   *
   * @param Object $search
   * @return Boolean if the queue contains the item
   *
   */
  public function search($search)
  {
    if ($this->isEmpty()) {
      return false;
    }

    for ($i = $this->start; $i <= $this->counter; $i++) {
      if (is_object($this->content[$i]) && ($this->content[$i] instanceof String)) {
        if ($this->content[$i]->equals($search))
          return true;
      }
      if ($this->content[$i] == $search) {
        return true;
      }
    }

    return false;
  }

  /**
   * Checks if the queue is empty
   *
   * @return boolean if the queue is empty
   */
  public function isEmpty()
  {
    return ($this->start == $this->counter);
  }

  /**
   * Clears the queue
   */
  public function clear()
  {
    $this->content = [];
    $this->counter = 0;
    $this->start = 0;
  }
}