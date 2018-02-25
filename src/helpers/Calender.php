<?php

namespace youconix\core\helpers;

/**
 * Calender generator class.
 * Generates a month calender
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
 */
class Calender extends \youconix\core\helpers\Helper {
    /**
     * 
     * @var \youconix\core\helpers\HTML
     */    
	protected $HTML;
	/**
	 * 
	 * @var \Language 
	 */
	protected $language;
	
	/**
	 * 
	 * @var \Output
	 */
	protected $template;
	
	protected $i_month;
	protected $i_year;
	protected $i_startDayWeek;
	protected $s_event;
	protected $a_items;
	protected $s_name = 'calender';
	
	/**
	 * PHP 5 constructor
	 *
	 * @param \youconix\core\helpers\html\HTML $HTML
	 * @param \Language $language
	 * @param \Output $template
	 */
	public function __construct(\youconix\core\helpers\html\HTML $HTML, \Language $language,\Output $template) {
		$this->i_month = date ( 'n' );
		$this->i_year = date ( 'Y' );
		$this->i_startDayWeek = 0; // sunday
		$this->HTML = $HTML;
		$this->language = $language;
		$this->template = $template;
		$this->s_event = '';
		$this->a_items = array ();
	}
	
	/**
	 * Return the set year
	 *
	 * @return int The set year
	 */
	public function getYear() {
		return $this->i_year;
	}
	
	/**
	 * Sets the year
	 *
	 * @param int $i_year
	 *        	The year
	 */
	public function setYear($i_year) {
		if ($i_year > 0)
			$this->i_year = $i_year;
	}
	
	/**
	 * Sets the month
	 *
	 * @param int $i_month
	 *        	The month
	 */
	public function setMonth($i_month) {
		if ($i_month > 0 && $i_month < 13) {
			$this->i_month = $i_month;
		} else if ($i_month == 0) {
			$this->i_month = 12;
			$this->i_year --;
		} else if ($i_month == 13) {
			$this->i_month = 1;
			$this->i_year ++;
		}
	}
	
	/**
	 * Sets the week start-date (0 == sunday)
	 *
	 * @param int $i_day
	 *        	The week start date
	 */
	public function setStartDay($i_day) {
		if ($i_day >= 0 && $i_day <= 6)
			$this->i_startDayWeek = $i_day;
	}
	
	/**
	 * Sets the callback event
	 *
	 * @param string $s_event
	 *        	the callback event
	 */
	public function setEvent($s_event) {
		$this->s_event = $s_event;
	}
	
	/**
	 * Sets the dark days for the calender
	 *
	 * @param array $a_items
	 *        	The days
	 */
	public function setItems($a_items) {
		$this->a_items = $a_items;
	}
	
	/**
	 * Generates the calender
	 *
	 * @param string $s_key    The template key to write to
	 */
	public function generateCalender($s_key) {
	    $style = $this->HTML->stylesheet('
        #calender {	width:215px; height:auto; }
        #calender li { width:30px; float:left; font-size:1em;}
        #calender li.bold { font-size:0.95em;}
	    ');
	    $this->template->setCSS($style->generateItem());
	    
	    $this->template->loadTemplate($s_key, NIV.'vendor'.DS.'youconix'.DS.'Core'.DS.'templates'.DS.'helpers'.DS.'calendar.tpl');
	    
	    // Write days
	    for($i = $this->i_startDayWeek; $i < 7; $i ++) {
	        $this->template->setBlock('calendar_day',array('name'=> $this->language->get ( 'system/weekdaysShort/day' . $i )) );
	    }
	    for($i = 0; $i < $this->i_startDayWeek; $i ++) {
	        $this->template->setBlock('calendar_day',array('name'=> $this->language->get ( 'system/weekdaysShort/day' . $i )) );
	    }
	    
	    $script = 'calendar.setWeekStart(' . $this->i_startDayWeek . ');
        calendar.setMonth(' . $this->i_month . ');
        calendar.setYear(' . $this->i_year . ');
        calendar.setData(' . json_encode ( $this->a_items ) . ');
        calendar.setCaller("' . $this->s_event . '");
        ';
	    
	    $a_months = array();
	    for($i=1; $i<=12; $i++){
	        $a_months[] = $i.' : '.$this->language->get('system/months/month'.$i);
	    }
	    
	    $script = $this->HTML->javascript('
	    calendar.setMonths({'.implode(',',$a_months).'});
        calendar.display();
        ');
	    $link = $this->HTML->javascriptLink('{NIV}js/calender.php');
        $this->template->setJavascript($script->generateItem());
        $this->template->setJavascriptLink($link->generateItem());
	}
}