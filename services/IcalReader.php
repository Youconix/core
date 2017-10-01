<?php

namespace youconix\core\services;

class IcalReader
{

  protected $s_url;
  protected $s_name;
  protected $i_toDo;
  protected $i_events;
  protected $a_cal;
  protected $s_lastKeyWord;
  protected $s_format;

  const CALENDAR_BEGIN = 'BEGIN:VCALENDAR';
  const CALENDAR_END = 'END:VCALENDAR';
  const DAYLIGHT_BEGIN = 'BEGIN:DAYLIGHT';
  const DAYLIGHT_END = 'END:DAYLIGHT';
  const EVENT_BEGIN = 'BEGIN:VEVENT';
  const EVENT_END = 'END:VEVENT';
  const STANDARD_BEGIN = 'BEGIN:STANDARD';
  const STANDARD_END = 'END:STANDARD';
  const TIMEZONE_BEGIN = 'BEGIN:VTIMEZONE';
  const TIMEZONE_END = 'END:VTIMEZONE';
  const TODO_BEGIN = 'BEGIN:VTODO';
  const TODO_END = 'END:VTODO';

  protected function reset()
  {
    $this->s_name = '';
    $this->i_toDo = 0;
    $this->i_events = 0;
    $this->a_cal = [
	'VEVENT' => [],
	'VTODO' => []
    ];
    $this->s_lastKeyWord = '';
  }

  /**
   * @param string $s_url
   * @param string $s_dateFormat
   */
  public function read($s_url, $s_dateFormat = 'd-m-Y')
  {
    if (!$s_url || trim($s_url) == '') {
      throw new \LogicException('No valid url given');
    }

    $this->reset();
    $this->s_url = $s_url;
    $this->s_format = $s_dateFormat;

    $a_lines = file($this->s_url, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if (stristr($a_lines[0], self::CALENDAR_BEGIN) === false) {
      throw new \LogicException('Url ' . $s_url . ' does not contain a valid ICS file.');
    }

    $this->parse($a_lines);

    $this->a_cal['VEVENT'] = $this->addTimestamps($this->a_cal['VEVENT']);
  }

  /**
   * 
   * @param array $a_lines
   */
  protected function parse(array $a_lines)
  {
    $s_type = '';
    foreach ($a_lines as $s_line) {
      $s_line = trim($s_line);

      if ($this->isIgnoreLine($s_line)) {
	continue;
      }

      $a_data = $this->line2KeyAndValue($s_line);
      if (is_null($a_data)) {
	$this->addComponent($s_type, $s_line);
      }

      list ($s_keyword, $s_value) = $a_data;

      switch ($s_line) {
	case self::TODO_BEGIN:
	  $this->i_toDo ++;
	  $s_type = 'VEVENT';
	  break;

	case self::EVENT_BEGIN:
	  $this->i_events ++;
	  $s_type = 'VEVENT';
	  break;

	case self::CALENDAR_BEGIN:
	case self::DAYLIGHT_BEGIN:
	case self::TIMEZONE_BEGIN:
	case self::STANDARD_BEGIN:
	  $s_type = $s_value;
	  break;

	case self::TODO_END:
	case self::EVENT_END:
	case self::CALENDAR_END:
	case self::DAYLIGHT_END:
	case self::TIMEZONE_END:
	case self::STANDARD_END:
	  $s_type = 'VCALENDAR';
	  break;

	default:
	  $this->addComponent($s_type, $s_value, $s_keyword);
	  break;
      }
    }
  }

  /**
   * 
   * @param array $set
   * @return int
   */
  protected function addTimestamps(array $set)
  {
    foreach ($set as $index => $item) {
      $set[$index]['TIMESTAMP'] = $this->icalDate2Timestamp($item['DTSTART']);
      $set[$index]['REAL_DATETIME'] = date($this->s_format, $set[$index]['TIMESTAMP']);

      if (array_key_exists('ALLDAYEVENT', $item) && ($item['ALLDAYEVENT'] != 'FALSE')) {
	$i_end = mktime(23, 59, 59, date('n', $set[$index]['TIMESTAMP']), date('j', $set[$index]['TIMESTAMP']), date('Y', $set[$index]['TIMESTAMP']));
      } else {
	$i_end = $this->icalDate2Timestamp($item['DTEND']);
      }
      $set[$index]['DURATION'] = ($i_end - $set[$index]['TIMESTAMP']);
    }

    return $set;
  }

  /**
   * 
   * @param string $s_line
   * @return boolean
   */
  protected function isIgnoreLine($s_line)
  {
    $a_ignoreLines = [
	'METHOD:',
	'PRODID:',
	'VERSION:',
	'TZID:',
	'X-MICROSOFT-DISALLOW-COUNTER'
    ];

    foreach ($a_ignoreLines as $s_test) {
      if (stristr($s_line, $s_test) !== false) {
	return true;
      }
    }

    return false;
  }

  /**
   * 
   * @param unknown $s_text
   * @return null|array
   */
  protected function line2KeyAndValue($s_text)
  {
    $a_matches = null;
    if (!preg_match("/([^:]+)[:]([\w\W]*)/", $s_text, $a_matches)) {
      return null;
    }

    $a_matches = array_splice($a_matches, 1, 2);
    return $a_matches;
  }

  /**
   * 
   * @param string $s_type
   * @param string $s_value
   * @param string $s_keyword
   */
  protected function addComponent($s_type, $s_value, $s_keyword = null)
  {
    if (is_null($s_keyword)) {
      $s_keyword = $this->s_lastKeyWord;

      switch ($s_type) {
	case 'VEVENT':
	  $s_value = $this->a_cal[$s_type][($this->i_events - 1)][$s_keyword] . $s_value;
	  break;

	case 'VTODO':
	  $s_value = $this->a_cal[$s_type][($this->i_toDo - 1)][$s_keyword] . $s_value;
	  break;
      }
    }

    if (stristr($s_keyword, 'DTSTART') || stristr($s_keyword, 'DTEND')) {
      $a_keyword = explode(';', $s_keyword);
      $s_keyword = $a_keyword[0];

      if (strpos($s_value, ':') !== false) {
	$a_value = explode(':', $s_value);
	$s_value = end($a_value);
      }
    }

    $s_keyword = str_replace('X-MICROSOFT-CDO-', '', $s_keyword);
    $s_keyword = str_replace('X-WR-', '', $s_keyword);

    switch ($s_type) {
      case 'VTODO':
	$this->a_cal[$s_type][($this->i_toDo - 1)][$s_keyword] = $s_value;
	break;

      case 'VEVENT':
	$this->a_cal[$s_type][($this->i_events - 1)][$s_keyword] = $s_value;
	break;

      default:
	$this->a_cal[$s_type][$s_keyword] = $s_value;
	break;
    }

    $this->s_lastKeyWord = $s_keyword;
  }

  protected function icalDate2Timestamp($s_date)
  {
    $s_date = str_replace([
	'T',
	'Z'
	], [
	'',
	''
	], $s_date);

    $a_date = null;
    $s_pattern = '/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{0,2})([0-9]{0,2})([0-9]{0,2})/';
    preg_match($s_pattern, $s_date, $a_date);

    $i_unixTimestamp = mktime((int) $a_date[4], (int) $a_date[5], (int) $a_date[6], (int) $a_date[2], (int) $a_date[3], (int) $a_date[1]);

    return $i_unixTimestamp;
  }

  /**
   * 
   * @return string
   */
  public function getName()
  {
    return trim($this->a_cal['VCALENDAR']['CALNAME']);
  }

  /**
   * 
   * @return array
   */
  public function getEvents()
  {
    return $this->a_cal['VEVENT'];
  }

  /**
   * 
   * @return boolean
   */
  public function hasEvents()
  {
    return (count($this->getEvents()) > 0);
  }

  /**
   * 
   * @param \DateTime $start
   * @param \DateTime $end
   * @return array
   */
  public function getEventsFromRange(\DateTime $start, \DateTime $end)
  {
    $i_start = $start->format('U');
    $i_end = $end->format('U');

    $a_events = [];

    foreach ($this->a_cal['VEVENT'] as $item) {
      if (($item['TIMESTAMP'] >= $i_start) && ($item['TIMESTAMP'] <= $i_end)) {
	$a_events[$item['TIMESTAMP']] = $item;
      }
    }

    ksort($a_events);

    return $a_events;
  }

  /**
   * 
   * @return array
   */
  public function getTasks()
  {
    return $this->a_cal['VTODO'];
  }

  /**
   * 
   * @return boolean
   */
  public function hasTasks()
  {
    return (count($this->hasTasks) > 0);
  }
}
