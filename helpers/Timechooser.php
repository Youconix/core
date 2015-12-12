<?php

/**
 * Calender generator class.
 * Generates a month calender with time selection field
 *
 * This file is part of Miniature-happiness                                    
 *                                                                              
 * @copyright Youconix                                
 * @author    Rachelle Scheijen                                                
 * @since     1.0                                                              
 *                                                                              
 * Miniature-happiness is free software: you can redistribute it and/or modify 
 * it under the terms of the GNU Lesser General Public License as published by  
 * the Free Software Foundation, either version 3 of the License, or            
 * (at your option) any later version.                                          
 *                                                                              
 * Miniature-happiness is distributed in the hope that it will be useful,      
 * but WITHOUT ANY WARRANTY; without even the implied warranty of               
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                
 * GNU General Public License for more details.                                 
 *                                                                              
 * You should have received a copy of the GNU Lesser General Public License     
 * along with Miniature-happiness.  If not, see <http://www.gnu.org/licenses/>.
 */
class Helper_Timechooser extends Helper
{

    private $service_Language;

    private $a_months = array();

    private $a_days = array();

    private $i_month;

    private $i_year;

    private $i_startDayWeek;

    private $s_callback = '';

    /**
     * PHP 5 constructor
     */
    public function __construct()
    {
        $this->i_month = date('n');
        $this->i_year = date('Y');
        $this->i_startDayWeek = 1; // monday
        $this->s_callback = '';
        
        $this->service_Language = Memory::services('Language');
        $this->a_months[0] = '""';
        for ($i = 1; $i <= 12; $i ++) {
            $this->a_months[$i] = '"' . $this->service_Language->get('language/months/month' . $i) . '"';
        }
        
        for ($i = 1; $i <= 7; $i ++) {
            $this->a_days[] = '"' . substr($this->service_Language->get('language/days/day' . $i), 0, 1) . '"';
        }
    }

    /**
     * Return the set year
     *
     * @return int The set year
     */
    public function getYear()
    {
        return $this->i_year;
    }

    /**
     * Sets the year
     *
     * @param int $i_year
     *            The year
     */
    public function setYear($i_year)
    {
        if ($i_year > 0)
            $this->i_year = $i_year;
    }

    /**
     * Sets the month
     *
     * @param int $i_month
     *            The month
     */
    public function setMonth($i_month)
    {
        if ($i_month > 0 && $i_month < 13)
            $this->i_month = $i_month;
        else 
            if ($i_month == 0) {
                $this->i_month = 12;
                $this->i_year --;
            } else 
                if ($i_month == 13) {
                    $this->i_month = 1;
                    $this->i_year ++;
                }
    }

    /**
     * Sets the week start-date (0 == sunday)
     *
     * @param int $i_day
     *            The week start date
     */
    public function setStartDay($i_day)
    {
        if ($i_day >= 0 && $i_day <= 6)
            $this->i_startDayWeek = $i_day;
    }

    /**
     * Sets the JS callback for the selection event
     *
     * @param string $s_class
     *            The JS callback
     */
    public function setCallback($s_callback)
    {
        $this->s_callback = $s_callback;
    }

    /**
     * Generates the calender
     *
     * @return string The calender
     */
    public function generate()
    {
        $s_form = '<div id="timeChooser">
    		<table id="calenderChooser">
    		<tbody>
    		</tbody>
    		</table>
    		<table id="timerChooser">
    		<thead>
    		<tr>
    			<td>' . $this->service_Language->get('language/date/hours') . '</td>
    			<td>' . $this->service_Language->get('language/date/minutes') . '</td>
    		</tr>
    		</thead>
    		<tbody>
    		<tr>
    			<td id="hourList">
    			
    			</td>
    			<td id="minuteList" rowspan="2">
    			</td>
    		</tr>
    		<tr>
    			<td style="height:80px"><br/></td>
    		</tr>
    		</tbody>
    		</table>
    	</div>
    	
    	' . $this->js();
        
        return $s_form;
    }

    private function js()
    {
        return '<script type="text/javascript">
    	<!--
    	function TimeChooser(){
    		this.months		= new Array(' . implode(',', $this->a_months) . ');
    		this.dayNames	= new Array(' . implode(',', $this->a_days) . ');
    		this.callback	= "' . $this->s_callback . '";
    		this.month	= ' . $this->i_month . ';
    		this.year	= ' . $this->i_year . ';
    		this.startDayWeek = ' . $this->i_startDayWeek . ';
    		
    		this.choosenMonth	= -1;
    		this.choosenYear	= -1;
    		this.choosenDay		= -1;
    		this.choosenHour	= -1;
    		this.choosenMinute	= -1;
    		
    		TimeChooser.prototype.init	= function(){
    			this.generateDays();
    			this.generateTime();
    			
    			this.choosenMonth	= -1;
	    		this.choosenYear	= -1;
	    		this.choosenDay		= -1;
	    		this.choosenHour	= -1;
	    		this.choosenMinute	= -1;
    		}
    		
    		TimeChooser.prototype.generateDays	= function(){
    			days	= getDaysMonth(this.month,this.year);
    			
    			$("#calenderChooser tbody").empty();
    			
    			header	= \'<td><a href="javascript:timeChooser.previousMonth()">&lt;&lt;</a></td> \
						<td class="bold textCenter"> \'+ this.months[this.month]+\' \'+ this.year+\' </td> \
						<td><a href="javascript:timeChooser.nextMonth()">&gt;&gt;</a></td>\';

				dayNames	= "";
				for(i=this.startDayWeek; i<7; i++){
					dayNames += "<span>"+this.dayNames[i]+"</span>";
				}
				for(i=0; i<this.startDayWeek; i++){
					dayNames += "<span>"+this.dayNames[i]+"</span>";
				}
    				
				output	= "";
				/* Generate start space */
				var d = new Date(this.year,this.month-1,1);
				var n = d.getDay()-this.startDayWeek;
				
				var current = new Date();
				current.setHours(23);
				current.setMinutes(59);
				current.setSeconds(59);
				
				currentDay	= 0;
				if( (this.year < current.getFullYear()) ||  (this.month-1) < current.getMonth() ){
					currentDay = 32;
				}
				else if( (this.month-1) == current.getMonth() ){
					currentDay = current.getDate();
				}
				
				if( n < 0 )	n = 7 + n;
				for(i=1; i<=n; i++){
					output += "<span>&nbsp;</span>";
				}
				
				/* Generate days */
    			for(i=1; i<=days; i++){
    				if( i <= currentDay ){
						output 	+= \'<span class="dayDisabled">\'+i+"</span>";
					}
					else {
						output 	+= \'<span id="day_\'+i+\'">\'+i+"</span>";
					}
	    		}
	    		
	    		$("#calenderChooser tbody").append("<tr> \
    				 "+header+ " \
    				</tr>");
    			$("#calenderChooser tbody").append(\'<tr id="dayNames">\
	    			<td><br/></td> \
	    			<td style="width:180px">\'+dayNames+\'</td> \
	    			<td><br/></td> \
	    		</tr>\');
	    		$("#calenderChooser tbody").append(\'<tr id="calenderDays">\
	    			<td><br/></td> \
	    			<td>\'+output+\'</td> \
	    			<td><br/></td> \
	    		</tr>\');
	    		
	    		for(i=1; i<=days; i++){
	    			if( i > currentDay ){
						$("#day_"+i).click(function(){ timeChooser.selectDay(this.id) });
					}
	    		}
    		}
    		
    		TimeChooser.prototype.generateTime	= function(){
    			$("#hourList").empty();
    			$("#minuteList").empty();
    			
    			for(i=0; i<=23; i++){
    				display	= i;
    				if( display < 10 )	display = "0"+display;
    			
    				hour 	= \'<span id="hour_\'+i+\'">\'+display+\'</span>\';
	    			$("#hourList").append(hour);
    			}
    			
    			for(i=0; i<=59; i++){
    				display	= i;
    				if( display < 10 )	display = "0"+display;
    			
    				minute 	= \'<span id="minute_\'+i+\'">\'+display+\'</span>\';
	    			$("#minuteList").append(minute);
    			}
    			
    			for(i=0; i<=59; i++){
    				if( i<=23 ){
    					$("#hour_"+i).click(function(){ timeChooser.selectHour(this.id) });
    				}
    			
    				$("#minute_"+i).click(function(){ timeChooser.selectMinute(this.id) });
    			}
    		}
    		
    		
    		TimeChooser.prototype.previousMonth	= function(){
    			this.month--;
    			if( this.month == 0 ){
    				this.month = 12;
    				this.year--;
    			} 
    		
    			this.init();
    		}
    		
    		TimeChooser.prototype.nextMonth	= function(){
    			this.month++;
    			if( this.month == 13 ){
    				this.month = 1;
    				this.year++;
    			}
    		
    			this.init();
    		}
    		
    		TimeChooser.prototype.selectDay	= function(id){    		
    			day = parseInt(id.replace("day_",""));
    			
    			if( this.choosenDay != -1 ){
    				$("#day_"+this.choosenDay).attr("class","");
    			}
    		
    			this.choosenMonth	= this.month;
    			this.choosenYear	= this.year;
    			this.choosenDay		= day;
    			
    			$("#day_"+this.choosenDay).attr("class","selected");
    			
    			this.check();
    		}
    		
    		TimeChooser.prototype.selectHour	= function(id){
    			hour = parseInt(id.replace("hour_",""));
    			
    			if( this.choosenHour != -1 ){
    				$("#hour_"+this.choosenHour).attr("class","");
    			}
    		
    			this.choosenHour	= hour;
    			$("#hour_"+this.choosenHour).attr("class","selected");
    			
    			this.check();
    		}
    		
    		TimeChooser.prototype.selectMinute	= function(id){
    			minute = parseInt(id.replace("minute_",""));
    			
    			if( this.choosenMinute != -1 ){
    				$("#minute_"+this.choosenMinute).attr("class","");
    			}
    		
    			this.choosenMinute	= minute;
    			$("#minute_"+this.choosenMinute).attr("class","selected");
    			
    			this.check();
    		}
    		
    		TimeChooser.prototype.check	= function(){
    			if( this.choosenMonth != -1 && this.choosenYear	!= -1 && this.choosenDay != -1 && this.choosenHour != -1 && this.choosenMinute != -1 ){
					eval(this.callback+"("+this.choosenMonth+","+this.choosenYear+","+this.choosenDay+","+this.choosenHour+","+this.choosenMinute+")");
					
					this.init();
    			}
    		}
    	}
    	
    	var timeChooser	= new TimeChooser();
    	timeChooser.init();
    	//-->
    	</script>';
    }
}