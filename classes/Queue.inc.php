<?php
namespace core\classes;

/**
 * Miniature-happiness is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Miniature-happiness is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Miniature-happiness. If not, see <http://www.gnu.org/licenses/>.
 *
 * Queue class.
 * This collection works with the principal first in, first out
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
 * @deprecated
 *
 * @see http://php.net/manual/en/class.splqueue.php
 */
class Queue
{

    private $a_content;

    private $i_start;

    private $i_counter;

    /**
     * Creates a new queue
     *
     * @param $a_content The
     *            of the queue, optional
     */
    public function __construct($a_content = array())
    {
        if (! \core\Memory::isTesting()) {
            trigger_error("This class has been deprecated in favour of SplQueue.", E_USER_DEPRECATED);
        }
        $this->clear();
        
        $this->addArray($a_content);
    }

    /**
     * Merges the given queue with this one
     *
     * @param Queue $obj_Queue
     *            queue
     * @throws Exception $obj_Queue if not a Queue
     */
    public function addQueue($obj_Queue)
    {
        if (! ($obj_Queue instanceof Queue)) {
            throw new \Exception("Can only add Queues");
        }
        
        while (! $obj_Queue->isEmpty()) {
            $this->push($obj_Queue->pop());
        }
    }

    /**
     * Adds the array to the queue
     *
     * @param array $a_content
     *            content to add
     */
    public function addArray($a_content)
    {
        foreach ($a_content as $item) {
            $this->push($item);
        }
    }

    /**
     * Pushes the item at the end of the queue
     *
     * @param mixed $item
     *            item
     */
    public function push($item)
    {
        $this->a_content[] = $item;
        $this->i_counter ++;
    }

    /**
     * Retrieves and removes the head of this queue, or null if this queue is empty.
     *
     *
     * @return mixed The first element of the queue.
     */
    public function pop()
    {
        if ($this->isEmpty())
            return null;
        
        $s_content = $this->a_content[$this->i_start];
        $this->a_content[$this->i_start] = null;
        $this->i_start ++;
        
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
        if ($this->isEmpty())
            return null;
        
        return $this->a_content[$this->i_start];
    }

    /**
     * Searches if the queue contains the given item
     *
     * @param Object $search
     *            item
     * @return Boolean if the queue contains the item
     *        
     */
    public function search($search)
    {
        if ($this->isEmpty()) {
            return false;
        }
        
        for ($i = $this->i_start; $i <= $this->i_counter; $i ++) {
            if (is_object($this->a_content[$i]) && ($this->a_content[$i] instanceof String)) {
                if ($this->a_content[$i]->equals($search))
                    return true;
            }
            if ($this->a_content[$i] == $search) {
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
        return ($this->i_start == $this->i_counter);
    }

    /**
     * Clears the queue
     */
    public function clear()
    {
        $this->a_content = array();
        $this->i_counter = 0;
        $this->i_start = 0;
    }
}