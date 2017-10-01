<?php
namespace youconix\core\entities;

class HitCollection extends \youconix\core\ORM\Entity implements \Iterator
{

    protected $a_items = array();

    protected $a_keys = array();

    protected $i_pos = 0;

    protected $i_length = 0;

    /**
     * Creates a new HitCollection
     *
     * @param int $i_startDate
     *            The start date as timestamp
     * @param int $i_endDate
     *            The end date as timestamp
     */
    public function __construct($i_startDate, $i_endDate)
    {
        while ($i_startDate < $i_endDate) {
            $item = new HitItem(0, $i_startDate);
            $s_key = $item->getKey();
            
            $this->a_items[$s_key] = $item;
            $this->a_keys[] = $s_key;
            $this->i_length ++;
            
            $i_startDate = mktime(0, 0, 0, date("n", $i_startDate) + 1, 1, date("Y", $i_startDate));
        }
    }

    /**
     * Adds a HitItem
     *
     * @param \youconix\core\models\data\HitItem $item
     *            The item
     */
    public function add(HitItem $item)
    {
        $s_key = $item->getKey();
        
        $this->a_items[$s_key]->increaseAmount($item->getAmount());
    }

    /**
     * Returns the current item
     *
     * @return \youconix\core\models\data\HitItem The item
     */
    public function current()
    {
        $s_key = $this->key();
        return $this->a_items[$s_key];
    }

    /**
     * Returns the key of the current item
     *
     * @return string The key
     */
    public function key()
    {
        return $this->a_keys[$this->i_pos];
    }

    /**
     * Switches to the next item
     */
    public function next()
    {
        $this->i_pos ++;
    }

    /**
     * Switches back to the first item
     */
    public function rewind()
    {
        $this->i_pos = 0;
    }

    /**
     * Returns if there are more items
     *
     * @return bool
     */
    public function valid()
    {
        return ($this->i_pos < $this->i_length);
    }
}

class HitItem
{

    protected $i_amount = 0;

    protected $s_key = '';

    protected $i_month;

    protected $i_year;

    /**
     * Creates a new HitItem
     *
     * @param int $i_amount
     *            The start amount
     * @param int $i_datetime
     *            The date as timestamp
     */
    public function __construct($i_amount, $i_datetime)
    {
        $this->i_amount = $i_amount;
        $this->i_month = date('n', $i_datetime);
        $this->i_year = date('Y', $i_datetime);
        $this->s_key = $this->i_month . '-' . $this->i_year;
    }

    /**
     * Returns the key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->s_key;
    }

    /**
     * Increases the stored amount with the given amount
     *
     * @param int $i_amount            
     */
    public function increaseAmount($i_amount)
    {
        $this->i_amount += $i_amount;
    }

    /**
     * Returns the stored amount
     *
     * @return int
     */
    public function getAmount()
    {
        return $this->i_amount;
    }
}
?>