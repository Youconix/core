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
 * Stack class.
 * This collection works with the principal first in, last out
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
 * @deprecated
 *
 * @see http://php.net/manual/en/class.splstack.php
 */
class Stack
{

    private $a_content;

    private $i_counter;

    /**
     * Creates a new stack
     *
     * @param $a_content The
     *            of the stack, optional
     */
    public function __construct($a_content = array())
    {
        if (! \core\Memory::isTesting()) {
            trigger_error("This class has been deprecated in favour of SplStack.", E_USER_DEPRECATED);
        }
        $this->clear();
        
        $this->addArray($a_content);
    }

    /**
     * Merges the given stack with this one
     *
     * @param Stack $obj_Stack
     *            stack
     * @throws Exception $obj_Stack if not a Stack
     */
    public function addStack($obj_Stack)
    {
        if (! ($obj_Stack instanceof Stack)) {
            throw new StackException("Can only add Stacks");
        }
        
        while (! $obj_Stack->isEmpty()) {
            $this->push($obj_Stack->pop());
        }
    }

    /**
     * Adds the array to the stack
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
     * Pushes the item at the end of the stack
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
        
        $s_content = $this->a_content[$this->i_counter];
        $this->a_content[$this->i_counter] = null;
        $this->i_counter --;
        
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
        
        return $this->a_content[$this->i_counter];
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
        for ($i = 0; $i <= $this->i_counter; $i ++) {
            if (is_object($this->a_content[$i]) && ($this->a_content[$i] instanceof String)) {
                if ($this->a_content[$i]->equals($search)) {
                    return true;
                }
            }
            if ($this->a_content[$i] == $search) {
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
        return ($this->i_counter == - 1);
    }

    /**
     * Clears the stack
     */
    public function clear()
    {
        $this->a_content = array();
        $this->i_counter = - 1;
    }
}

class StackException extends \Exception
{
}