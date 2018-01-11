<?php

namespace youconix\core\repositories;

/**
 * Statistics 
 * @author Rachelle Scheijen
 * @since 1.0
 */
class Stats extends \youconix\core\ORM\Repository
{

  protected $i_date;

  /**
   * PHP5 constructor
   *
   * @param \Builder $builder            
   * @param \Validation $validation            
   */
  public function __construct(\EntityManager $manager,
			      \youconix\core\entities\HitCollection $hitCollection, \Builder $builder
  )
  {
    parent::__construct($manager, $hitCollection, $builder);

    $this->i_date = mktime(0, 0, 0, date("n"), 1, date("Y"));
  }

  /**
   * 
   * @param int $startDate
   * @param int $endDate
   * @return \youconix\core\entities\HitCollection
   */
  protected function createHitCollection($startDate, $endDate)
  {
    $collection = clone $this->model;
    $collection->init($startDate, $endDate);

    return $collection;
  }

  /**
   * 
   * @param string $fingerprint
   * @return boolean if the visit is unique
   */
  public function saveVisit($fingerprint)
  {
    \youconix\core\Memory::type('string', $fingerprint);

    $i_datetime = mktime(date("H"), 0, 0, date("n"), date('j'), date("Y"));
    $database = $this->builder->update('stats_visits')
	->bindLiteral('amount', 'amount + 1')
	->getWhere()
	->bindInt('datetime', $i_datetime)
	->getResult();

    if ($database->affected_rows() == 0) {
      $this->builder->insert('stats_visits')
	  ->bindInt('amount', 1)
	  ->bindInt('datetime', $i_datetime)
	  ->getResult();
    }

    $periodStart = mktime(0, 0, 0, date("n"), 1, date("Y"));
    $periodEnd = new \DateTime('now');
    $periodEnd->modify('last day of this month')->setTime(23, 59, 59);

    $database = $this->builder->select('stats_unique', '*')
	->getWhere()
	->bindInt('datetime', [$periodStart, $periodEnd->getTimestamp()], 'AND',
	   'BETWEEN')
	->bindString('fingerprint', $fingerprint)
	->getResult();

    if ($database->num_rows() > 0) {
      return false;
    }
    $this->builder->insert('stats_unique')
	->bindInt('datetime', time())
	->bindString('fingerprint', $fingerprint)
	->getResult();

    return true;
  }

  /**
   * 
   * @param string $page
   */
  public function savePageHit($page)
  {
    \youconix\core\Memory::type('string', $page);

    $datetime = mktime(date("H"), 0, 0, date("n"), date('j'), date("Y"));

    $this->builder->update('stats_pages')
	->bindLiteral('amount', 'amount + 1')
	->getWhere()
	->bindInt('datetime', $datetime)
	->bindString('name', $page);

    $database = $this->builder->getResult();

    if ($database->affected_rows() == 0) {
      $this->builder->insert('stats_pages')
	  ->bindInt('amount', 1)
	  ->bindInt('datetime', $datetime)
	  ->bindString('name', $page)
	  ->getResult();
    }
  }

  /**
   * Saves the visitors OS
   *
   * @param string $s_os
   * @param string $s_osType
   */
  public function saveOS($s_os, $s_osType)
  {
    \youconix\core\Memory::type('string', $s_os);
    \youconix\core\Memory::type('string', $s_osType);

    $this->builder->update('stats_OS')
	->bindLiteral('amount', 'amount + 1')
	->getWhere()
	->bindInt('datetime', $this->i_date)
	->bindString('name', $s_os)
	->bindString('type', $s_osType);
    $database = $this->builder->getResult();

    if ($database->affected_rows() == 0) {
      $this->builder->insert('stats_OS')
	  ->bindString('name', $s_os)
	  ->bindInt('amount', 1)
	  ->bindInt('datetime', $this->i_date)
	  ->bindString('type', $s_osType)
	  ->getResult();
    }
  }

  /**
   * Saves the visitors browser
   *
   * @param string $s_browser
   * @param string $s_version
   */
  public function saveBrowser($s_browser, $s_version)
  {
    \youconix\core\Memory::type('string', $s_browser);
    \youconix\core\Memory::type('string', $s_version);

    $this->builder->update('stats_browser')
	->bindLiteral('amount', 'amount + 1')
	->getWhere()
	->bindInt('datetime', $this->i_date)
	->bindString('name', $s_browser)
	->bindString('version', $s_version);

    $database = $this->builder->getResult();
    if ($database->affected_rows() == 0) {
      $this->builder->insert('stats_browser')
	  ->bindString('name', $s_browser)
	  ->bindInt('amount', 1)
	  ->bindInt('datetime', $this->i_date)
	  ->bindString('version', $s_version)
	  ->getResult();
    }
  }

  /**
   * Saves the visitors reference
   *
   * @param string $s_reference
   */
  public function saveReference($s_reference)
  {
    \youconix\core\Memory::type('string', $s_reference);

    $s_reference = str_replace([
	'\\',
	'http://',
	'https://'
	], [
	'/',
	'',
	''
	], $s_reference);
    $a_reference = explode('/', $s_reference);
    $s_reference = $a_reference[0];

    $this->builder->update('stats_reference')
	->bindLiteral('amount', 'amount + 1')
	->getWhere()
	->bindInt('datetime', $this->i_date)
	->bindString('name', $s_reference);
    $database = $this->builder->getResult();

    if ($database->affected_rows() == 0) {
      $this->builder->insert('stats_reference')
	  ->bindString('name', $s_reference)
	  ->bindInt('amount', 1)
	  ->bindInt('datetime', $this->i_date)
	  ->getResult();
    }
  }

  /**
   * Saves the visitors screen size
   *
   * @param int $i_width
   * @param int $i_height
   */
  public function saveScreenSize($i_width, $i_height)
  {
    \youconix\core\Memory::type('int', $i_width);
    \youconix\core\Memory::type('int', $i_height);

    $this->builder->update('stats_screenSizes')
	->bindLiteral('amount', 'amount + 1')
	->getWhere()
	->bindInt('datetime', $this->i_date)
	->bindInt('width', $i_width)
	->bindInt('height', $i_height);
    $database = $this->builder->getResult();

    if ($database->affected_rows() == 0) {
      $this->builder->insert('stats_screenSizes')
	  ->bindInt('width', $i_width)
	  ->bindInt('height', $i_height)
	  ->bindInt('amount', 1)
	  ->bindInt('datetime', $this->i_date)
	  ->getResult();
    }
  }

  /**
   * Saves the visitors screen colors
   *
   * @param string $s_screenColors
   */
  public function saveScreenColors($s_screenColors)
  {
    \youconix\core\Memory::type('string', $s_screenColors);

    $this->builder->update('stats_screenColors')
	->bindLiteral('amount', 'amount + 1')
	->getWhere()
	->bindInt('datetime', $this->i_date)
	->bindString('name', $s_screenColors);
    $database = $this->builder->getResult();

    if ($database->affected_rows() == 0) {
      $this->builder->insert('stats_screenColors')
	  ->bindString('name', $s_screenColors)
	  ->bindInt('amount', 1)
	  ->bindInt('datetime', $this->i_date)
	  ->getResult();
    }
  }

  /**
   * Returns the visitors pro month
   *
   * @param int $startDate
   * @param int $endDate
   * @return \youconix\core\entities\HitCollection
   */
  public function getVisitors($startDate, $endDate)
  {
    \youconix\core\Memory::type('int', $startDate);
    \youconix\core\Memory::type('int', $endDate);

    $visitors = $this->createHitCollection($startDate, $endDate);

    $this->builder->select('stats_visits', 'amount,datetime')
	->group('datetime')
	->getWhere()
	->bindInt('datetime', [
	    $startDate,
	    $endDate
	    ], 'AND', 'BETWEEN');
    $database = $this->builder->getResult();

    if ($database->num_rows() > 0) {
      $visitorsPre = $database->fetch_object();

      foreach ($visitorsPre as $visitor) {
	$visitors->createHitItem($visitor);
      }
    }

    return $visitors;
  }

  /**
   * Returns the unique visitors from the given month
   *
   * @param int $startDate
   * @param int $endDate
   * @return \youconix\core\entities\HitCollection
   */
  public function getUnique($startDate, $endDate)
  {
    \youconix\core\Memory::type('int', $startDate);
    \youconix\core\Memory::type('int', $endDate);

    $unique = $this->createHitCollection($startDate, $endDate);

    $this->builder->select('stats_unique', 'datetime')
	->group('datetime')
	->getWhere()
	->bindInt('datetime', [
	    $startDate,
	    $endDate
	    ], 'AND', 'BETWEEN');
    $database = $this->builder->getResult();

    if ($database->num_rows() > 0) {
      $uniquePre = $database->fetch_object();

      foreach ($uniquePre as $item) {
	$item->amount = 1;
	$unique->createHitItem($item);
      }
    }

    return $unique;
  }

  /**
   * Returns the hits pro hour
   *
   * @param int $i_startDate
   *            The start date as timestamp
   * @param int $i_endDate
   *            The end date as timestamp
   * @return array The hits
   */
  public function getHitsHours($i_startDate, $i_endDate)
  {
    \youconix\core\Memory::type('int', $i_startDate);
    \youconix\core\Memory::type('int', $i_endDate);

    $a_hits = [];
    for ($i = 0; $i <= 23; $i ++) {
      $a_hits[$i] = 0;
    }

    $this->builder->select('stats_hits', 'amount,datetime')
	->group('datetime')
	->getWhere()
	->bindInt('datetime', [
	    $i_startDate,
	    $i_endDate
	    ], 'AND', 'BETWEEN');
    $database = $this->builder->getResult();

    if ($database->num_rows() > 0) {
      $a_hitsPre = $database->fetch_assoc();

      foreach ($a_hitsPre as $a_hit) {
	$a_hits[date('H', $a_hit['datetime'])] += $a_hit['amount'];
      }
    }

    return $a_hits;
  }

  /**
   * Returns the pages
   *
   * @param int $i_startDate
   *            The start date as timestamp
   * @param int $i_endDate
   *            The end date as timestamp
   * @return array The pages
   */
  public function getPages($i_startDate, $i_endDate)
  {
    \youconix\core\Memory::type('int', $i_startDate);
    \youconix\core\Memory::type('int', $i_endDate);

    $a_pages = [];
    $this->builder->select('stats_pages', 'name,SUM(amount) AS amount')
	->group('name')
	->order('amount', 'DESC')
	->getWhere()
	->bindInt('datetime', [
	    $i_startDate,
	    $i_endDate
	    ], 'AND', 'BETWEEN');
    $database = $this->builder->getResult();

    if ($database->num_rows() > 0) {
      $a_pages = $database->fetch_assoc();
    }

    return $a_pages;
  }
  
  /**
   * Returns the page visits pro month
   *
   * @param int $i_startDate
   *            The start date as timestamp
   * @param int $i_endDate
   *            The end date as timestamp
   * @return array The pages
   */
  public function getPageVisits($i_startDate, $i_endDate)
  {
    \youconix\core\Memory::type('int', $i_startDate);
    \youconix\core\Memory::type('int', $i_endDate);

    $a_pages = [];
    $this->builder->select('stats_pages', 'amount,datetime')
	->group('name')
	->order('amount', 'DESC')
	->getWhere()
	->bindInt('datetime', [
	    $i_startDate,
	    $i_endDate
	    ], 'AND', 'BETWEEN');
    $database = $this->builder->getResult();

    if ($database->num_rows() > 0) {
      $a_pagesRaw = $database->fetch_assoc();
      foreach($a_pagesRaw as $page){
	$key = date('n-Y', $page['datetime']);
	if (!array_key_exists($key, $a_pages)) {
	  $a_pages[$key] = 0;
	}
	
	$a_pages[$key] += $page['amount'];
      }
    }

    return $a_pages;
  }

  /**
   * Sorts the dates
   *
   * @param array $a_data
   *            The dates
   * @return array
   */
  protected function sortDate($a_data)
  {
    $a_items = [];
    $a_data2 = [];
    foreach ($a_data as $a_item) {
      if (!array_key_exists($a_item['type'], $a_data2)) {
	$a_data2[$a_item['type']] = [];
      }

      $a_data2[$a_item['type']][str_replace(' ', '', $a_item['name'])] = $a_item;
    }

    ksort($a_data2);
    foreach ($a_data2 as $key => $item) {
      ksort($a_data2[$key]);
    }

    foreach ($a_data2 as $key => $type) {
      foreach ($a_data2[$key] as $item) {
	$a_items[] = $item;
      }
    }

    return $a_items;
  }

  /**
   * Returns the operating systems
   *
   * @param int $i_startDate
   *            The start date as timestamp
   * @param int $i_endDate
   *            The end date as timestamp
   * @return array The operating systems
   */
  public function getOS($i_startDate, $i_endDate)
  {
    \youconix\core\Memory::type('int', $i_startDate);
    \youconix\core\Memory::type('int', $i_endDate);

    $a_OS = [];
    $this->builder->select('stats_OS', 'id,name,amount,type')
	->getWhere()
	->bindInt('datetime', [
	    $i_startDate,
	    $i_endDate
	    ], 'AND', 'BETWEEN');
    $database = $this->builder->getResult();

    if ($database->num_rows() > 0) {
      $a_data = $database->fetch_assoc();

      $a_OS = $this->sortDate($a_data);
    }

    return $a_OS;
  }

  /**
   * Returns the browsers
   * Grouped by browser
   *
   * @param int $i_startDate
   *            The start date as timestamp
   * @param int $i_endDate
   *            The end date as timestamp
   * @return array The browsers
   */
  public function getBrowsers($i_startDate, $i_endDate)
  {
    \youconix\core\Memory::type('int', $i_startDate);
    \youconix\core\Memory::type('int', $i_endDate);

    $a_browsers = [];
    $this->builder->select('stats_browser',
			   'id,name AS type,amount,CONCAT(name," ",version) AS name')
	->group('name')
	->order('amount', 'DESC')
	->getWhere()
	->bindInt('datetime', [
	    $i_startDate,
	    $i_endDate
	    ], 'AND', 'BETWEEN');
    $database = $this->builder->getResult();

    if ($database->num_rows() > 0) {
      $a_data = $database->fetch_assoc_key('name');
      $a_browsers = $this->sortDate($a_data);
    }

    return $a_browsers;
  }

  /**
   * Returns the screen colors
   *
   * @param int $i_startDate
   *            The start date as timestamp
   * @param int $i_endDate
   *            The end date as timestamp
   * @return array The screen colors
   */
  public function getScreenColors($i_startDate, $i_endDate)
  {
    \youconix\core\Memory::type('int', $i_startDate);
    \youconix\core\Memory::type('int', $i_endDate);

    $a_screenColors = array();
    $this->builder->select('stats_screenColors', 'name,amount')
	->getWhere()
	->bindInt('datetime', [
	    $i_startDate,
	    $i_endDate
	    ], 'AND', 'BETWEEN');
    $database = $this->builder->getResult();

    if ($database->num_rows() > 0) {
      $a_screenColors = $database->fetch_assoc();
    }

    return $a_screenColors;
  }

  /**
   * Returns the screen sizes
   *
   * @param int $i_startDate
   *            The start date as timestamp
   * @param int $i_endDate
   *            The end date as timestamp
   * @return array The screen sizes
   */
  public function getScreenSizes($i_startDate, $i_endDate)
  {
    \youconix\core\Memory::type('int', $i_startDate);
    \youconix\core\Memory::type('int', $i_endDate);

    $a_screenSizes = array();
    $this->builder->select('stats_screenSizes', 'width,height,amount')
	->order('width', 'DESC', 'height', 'DESC')
	->getWhere()
	->bindInt('datetime', [
	    $i_startDate,
	    $i_endDate
	    ], 'AND', 'BETWEEN');
    $database = $this->builder->getResult();

    if ($database->num_rows() > 0) {
      $a_screenSizes = $database->fetch_assoc();
    }

    return $a_screenSizes;
  }

  /**
   * Returns the references
   *
   * @param int $i_startDate
   *            The start date as timestamp
   * @param int $i_endDate
   *            The end date as timestamp
   * @return array The references
   */
  public function getReferences($i_startDate, $i_endDate)
  {
    \youconix\core\Memory::type('int', $i_startDate);
    \youconix\core\Memory::type('int', $i_endDate);

    $a_references = [];
    $this->builder->select('stats_reference', 'SUM(amount) AS amount,name')
	->order('amount', 'DESC')
	->group('name')
	->getWhere()
	->bindInt('datetime', [
	    $i_startDate,
	    $i_endDate
	    ], 'AND', 'BETWEEN');
    $database = $this->builder->getResult();

    if ($database->num_rows() > 0) {
      $a_references = $database->fetch_assoc();
    }

    return $a_references;
  }

  /**
   * Returns the lowest date saved as a timestamp
   *
   * @return int the lowest date
   */
  public function getLowestDate()
  {
    $i_date = - 1;
    $this->builder->select('stats_hits',
			   $this->builder->getMinimun('datetime', 'date'));
    $database = $this->builder->getResult();

    if ($database->num_rows() > 0) {
      $i_date = (int) $database->result(0, 'date');
    }

    return $i_date;
  }

  /**
   * Cleans the stats from a year old
   *
   * @throws DBException If the clearing failes
   */
  public function cleanStatsYear()
  {
    $i_time = mktime(date("H"), date("i"), date("s"), date("n"), date("j"),
								      date("Y") - 1);
    $this->cleanStats($i_time);
  }

  /**
   * Cleans the stats from a month old
   *
   * @throws DBException If the clearing failes
   */
  public function cleanStatsMonth()
  {
    $i_time = mktime(date("H"), date("i"), date("s"), date("n") - 1, date("j"),
									  date("Y"));
    $this->cleanStats($i_time);
  }

  /**
   * Deletes the stats older than the given timestamp
   *
   * @param int $i_maxDate
   *            minimun timestamp to keep data
   * @throws DBException If the clearing failes
   */
  protected function cleanStats($i_maxDate)
  {
    \youconix\core\Memory::type('int', $i_maxDate);

    try {
      $this->builder->transaction();

      $this->builder->delete('stats_hits')
	  ->getWhere()
	  ->bindInt('datetime', $i_maxDate, 'AND', '<')
	  ->getResult();
      $this->builder->delete('stats_pages')
	  ->getWhere()
	  ->bindInt('datetime', $i_maxDate, 'AND', '<')
	  ->getResult();
      $this->builder->delete('stats_unique')
	  ->getWhere()
	  ->bindInt('datetime', $i_maxDate, 'AND', '<')
	  ->getResult();
      $this->builder->delete('stats_screenSizes')
	  ->getWhere()
	  ->bindInt('datetime', $i_maxDate, 'AND', '<')
	  ->getResult();
      $this->builder->delete('stats_screenColors')
	  ->getWhere()
	  ->bindInt('datetime', $i_maxDate, 'AND', '<')
	  ->getResult();
      $this->builder->delete('stats_browser')
	  ->getWhere()
	  ->bindInt('datetime', $i_maxDate, 'AND', '<')
	  ->getResult();
      $this->builder->delete('stats_reference')
	  ->getWhere()
	  ->bindInt('datetime', $i_maxDate, 'AND', '<')
	  ->getResult();
      $this->builder->delete('stats_OS')
	  ->getWhere()
	  ->bindInt('datetime', $i_maxDate, 'AND', '<')
	  ->getResult();

      $this->builder->commit();
    } catch (\DBException $e) {
      $this->builder->rollback();
      throw $e;
    }
  }
}
