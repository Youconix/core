<?php

namespace core\helpers;

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
 * Calender generator class.
 * Generates a month calender
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
 */
class Calender extends Helper {
	protected $helper_HTML;
	protected $service_Language;
	protected $i_month;
	protected $i_year;
	protected $i_startDayWeek;
	protected $s_event;
	protected $a_items;
	protected $s_name = 'calender';
	
	/**
	 * PHP 5 constructor
	 *
	 * @param \core\helpers\html\HTML $helper_HTML
	 *        	service
	 * @param \Language $service_Language
	 *        	service
	 */
	public function __construct(\core\helpers\html\HTML $helper_HTML, \Language $service_Language) {
		$this->i_month = date ( 'n' );
		$this->i_year = date ( 'Y' );
		$this->i_startDayWeek = 0; // sunday
		$this->helper_HTML = $helper_HTML;
		$this->service_Language = $service_Language;
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
	 * @return string The calender
	 */
	public function generateCalender() {
		$s_view = '<style type="text/css">
    <!--
    #calender {	width:215px; height:auto; }
    #calender li { width:30px; float:left; font-size:1em;}
    #calender li.bold { font-size:0.95em;}
    //-->
    </style>

    <div id="calender">
    <table>
    <thead>
    <tr>
    <td><span class="link" onclick="calender.decreaseMonth()">&lt;&lt;</span></td>
    <td class="textCenter" id="calender_month"></td>
    <td><span class="link" onclick="calender.increaseMonth()">&gt;&gt;</span></td>
    </tr>
    <tr>
    <td colspan="3"><ul>';
		for($i = $this->i_startDayWeek; $i < 7; $i ++) {
			$s_view .= '<li class="bold">' . $this->service_Language->get ( 'system/weekdaysShort/day' . $i ) . '</li>';
		}
		for($i = 0; $i < $this->i_startDayWeek; $i ++) {
			$s_view .= '<li class="bold">' . $this->service_Language->get ( 'system/weekdaysShort/day' . $i ) . '</li>';
		}
		$s_view .= '</ul></tr>
    </thead>
    <tbody>
    <tr>
    <td colspan="3"><ul id="calender_days"></ul></td>
    </tr>
    </tbody>
    </table>
    </div>

    <script type="text/javascript">
    <!--
    calender.setWeekStart(' . $this->i_startDayWeek . ');
    calender.setMonth(' . $this->i_month . ');
    calender.setYear(' . $this->i_year . ');
    calender.setData(' . json_encode ( $this->a_items ) . ');
    calender.setCaller("' . $this->s_event . '");
    calender.display();

    $("head").append(\'<script src="{NIV}js/calender.php"></script>\');
    //-->
    </script>
    ';
		
		return $s_view;
	}
}