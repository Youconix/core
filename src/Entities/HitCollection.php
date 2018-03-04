<?php

namespace youconix\Core\Entities;

class HitCollection extends \youconix\Core\ORM\AbstractEntity implements \Iterator
{

  protected $items = [];
  protected $keys = [];
  protected $pos = 0;
  protected $length = 0;

  /**
   * 
   * @param \BuilderInterface $builder
   * @param \ValidationInterface $validation
   */
  public function __construct(\BuilderInterface $builder, \ValidationInterface $validation)
  {
    $this->builder = $builder;
    $this->validation = $validation;

    $this->detectTableName();
  }

  /**
   * @param int $startDate
   *            The start date as timestamp
   * @param int $endDate
   *            The end date as timestamp
   */
  public function init($startDate, $endDate)
  {
    while ($startDate < $endDate) {
      $item = new HitItem(0, $startDate);
      $key = $item->getKey();

      $this->items[$key] = $item;
      $this->keys[] = $key;
      $this->length ++;

      $startDate = mktime(0, 0, 0, date("n", $startDate) + 1, 1,
					  date("Y", $startDate));
    }
  }
  
  /**
   * 
   * @param \stdClass $item
   */
  public function createHitItem(\stdClass $item)
  {
    $this->add( new HitItem($item->amount, $item->datetime) );
  }

  /**
   * Adds a HitItem
   *
   * @param \youconix\Core\Entities\HitItem $item
   */
  public function add(HitItem $item)
  {
    $key = $item->getKey();

    $this->items[$key]->increaseAmount($item->getAmount());
  }

  /**
   * Returns the current item
   *
   * @return \youconix\Core\Entities\HitItem
   */
  public function current()
  {
    $key = $this->key();
    return $this->items[$key];
  }

  /**
   * Returns the key of the current item
   *
   * @return string The key
   */
  public function key()
  {
    return $this->keys[$this->pos];
  }

  /**
   * Switches to the next item
   */
  public function next()
  {
    $this->pos ++;
  }

  /**
   * Switches back to the first item
   */
  public function rewind()
  {
    $this->pos = 0;
  }

  /**
   * Returns if there are more items
   *
   * @return bool
   */
  public function valid()
  {
    return ($this->pos < $this->length);
  }
}

class HitItem
{

  protected $amount = 0;
  protected $key = '';
  protected $month;
  protected $year;

  /**
   * Creates a new HitItem
   *
   * @param int $amount
   *            The start amount
   * @param int $datetime
   *            The date as timestamp
   */
  public function __construct($amount, $datetime)
  {
    $this->amount = $amount;
    $this->month = date('n', $datetime);
    $this->year = date('Y', $datetime);
    $this->key = $this->month . '-' . $this->year;
  }

  /**
   * Returns the key
   *
   * @return string
   */
  public function getKey()
  {
    return $this->key;
  }

  /**
   * Increases the stored amount with the given amount
   *
   * @param int $amount            
   */
  public function increaseAmount($amount)
  {
    $this->amount += $amount;
  }

  /**
   * Returns the stored amount
   *
   * @return int
   */
  public function getAmount()
  {
    return $this->amount;
  }
}