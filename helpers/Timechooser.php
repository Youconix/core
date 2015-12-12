<?php
namespace youconix\core\helpers;

/**
 * Calender generator class.
 * Generates a month calender with time selection field
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
 */
class Timechooser extends \youconix\core\helpers\Helper
{

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
    
    /**
     * 
     * @var \youconix\core\helpers\HTML
     */
    protected $HTML;
    
    protected $a_months = array();

    protected $a_days = array();

    protected $i_month;

    protected $i_year;

    protected $i_startDayWeek;

    protected $s_callback = '';

    /**
     * PHP 5 constructor
     */
    public function __construct(\Language $language,\Output $template,\youconix\core\helpers\HTML $HTML)
    {
        $this->i_month = date('n');
        $this->i_year = date('Y');
        $this->i_startDayWeek = 1; // monday
        $this->s_callback = '';
        
        $this->language = $language;
        $this->template = $template;
        $this->HTML = $HTML;
        $this->a_months[0] = '""';
        for ($i = 1; $i <= 12; $i ++) {
            $this->a_months[$i] = '"' . $this->language->get('language/months/month' . $i) . '"';
        }
        
        for ($i = 1; $i <= 7; $i ++) {
            $this->a_days[] = '"' . substr($this->language->get('language/days/day' . $i), 0, 1) . '"';
        }
    }

    /**
     *
     * @return int
     */
    public function getYear()
    {
        return $this->i_year;
    }

    /**
     *
     * @param int $i_year            
     */
    public function setYear($i_year)
    {
        if ($i_year > 0)
            $this->i_year = $i_year;
    }

    /**
     *
     * @param int $i_month            
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
     */
    public function setStartDay($i_day)
    {
        if ($i_day >= 0 && $i_day <= 6)
            $this->i_startDayWeek = $i_day;
    }

    /**
     * Sets the JS callback for the selection event
     *
     * @param string $s_callback
     */
    public function setCallback($s_callback)
    {
        $this->s_callback = $s_callback;
    }

    /**
     * Generates the calender
     *
     * @param string  $s_key    The template key to write to
     */
    public function generate()
    {
        $this->template->loadTemplate($s_key, NIV.DS.'vendor'.DS.'youconix'.DS.'core'.DS.'templates'.DS.'helpers'.DS.'timechooser.tpl');
        $this->template->set('chooser_hours',$this->language->get('language/date/hours'));
        $this->template->set('chooser_minutes',$this->language->get('language/date/minutes'));
    	    	
    	
    	$this->js();
    }

    /**
     * Generates the JS code
     */
    protected function js()
    {
        $a_js = array(
            'months' => implode(',', $this->a_months),
            'dayNames' => implode(',', $this->a_days),
            'callback' => $this->s_callback,
            'month' => $this->i_month,
            'year'=> $this->i_year,
            'startDayWeek' => $this->i_startDayWeek
         );
        $this->template->set('chooser_js',json_encode($a_js));
        
        $js = $this->HTML->javascriptLink(NIV.'vendor/youconix/core/js/timechooser.js');
        $this->template->setJavascriptLink($js->generateItem());
    }
}