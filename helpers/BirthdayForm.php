<?php
namespace\youconix\core\helpers;

/**
 * Birthday form widget
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
 */
class BirthdayForm extends \youconix\core\helpers\DateForm
{

    protected $s_className;

    protected $s_callback;

    protected $s_dayID;

    protected $s_monthID;

    protected $s_yearID;

    /**
     * Resets the form
     */
    public function reset()
    {
        $this->s_className = 'birthdayForm';
        $this->s_callback = '';
        $this->i_endYear = date('Y');
        $this->i_startYear = $this->i_endYear - 100;
        
        $this->i_daySelected = - 1;
        $this->i_monthSelected = - 1;
        $this->i_yearSelected = - 1;
        $this->s_scheme = 'd-m-y';
    }

    /**
     * Sets the javascript class name.
     * Call this function if you have more then one birthday form.
     * The default value is "birthdayForm"
     *
     * @param string $s_name
     *            name
     */
    public function setClassName($s_name)
    {
        $this->s_className = $s_name;
    }

    /**
     * Sets the end year
     *
     * @param int $i_year
     *            year
     * @throws \DateException the end year is in the future
     * @throws \DateException the end year is lower then the start year
     */
    public function setEndYear($i_year)
    {
        if ($i_year > date('Y'))
            throw new DateException("Selecting year " . $i_year . " in the future.");
        
        if ($i_year < $this->i_startYear)
            throw new DateException("Selecting year " . $i_year . " before start year " . $this->i_startYear . ".");
        
        $this->i_endYear = $i_year;
    }

    /**
     * Sets the start year
     *
     * @param int $i_year
     *            year
     * @throws \DateException the start year is in the future
     * @throws \DateException the start year is higer then the end year
     */
    public function setStartYear($i_year)
    {
        if ($i_year > date('Y'))
            throw new DateException("Selecting year " . $i_year . " in the future.");
        
        if ($i_year > $this->i_endYear)
            throw new DateException("Selecting year " . $i_year . " after end year " . $this->i_startYear . ".");
        
        $this->i_startYear = $i_year;
    }

    /**
     * Sets the javascript callback
     *
     * @param string $s_callback
     *            callback
     */
    public function setCallback($s_callback)
    {
        $this->s_callback = $s_callback;
    }

    /**
     * Generates the form
     *
     * @param string $s_idDay
     *            day form name, default birthday
     * @param string $s_idMonth
     *            month form name, default birthmonth
     * @param string $s_idYear
     *            year form name, default birthyear
     * @return string generated form
     */
    public function generate($s_idDay = 'birthday', $s_idMonth = 'birthmonth', $s_idYear = 'birthyear')
    {
        $this->s_dayID = $s_idDay;
        $this->s_monthID = $s_idMonth;
        $this->s_yearID = $s_idYear;
        
        /* Generate day list */
        $obj_day = $this->generateList(1, 31, $this->i_daySelected, $s_idDay, 'setBirthDay');
        
        /* Generate month list */
        $obj_month = $this->generateList(1, 12, $this->i_monthSelected, $s_idMonth, 'setBirthMonth');
        
        /* Generate year list */
        $obj_year = $this->generateList($this->i_startYear, $this->i_endYear, $this->i_yearSelected, $s_idYear, 'setBirthYear');
        
        /* Generate widget */
        $obj_out = $this->helper_HTML->div();
        $obj_out->setID('birthdayForm')->setClass('widget');
        $s_content = $this->generateJS() . "\n";
        if ($this->s_scheme == 'd-m-y') {
            $s_content .= $obj_day->generateItem() . ' ' . $obj_month->generateItem() . ' ' . $obj_year->generateItem();
        } else 
            if ($this->s_scheme == 'm-d-y') {
                $s_content .= $obj_month->generateItem() . ' ' . $obj_day->generateItem() . ' ' . $obj_year->generateItem();
            } else 
                if ($this->s_scheme == 'y-m-d') {
                    $s_content .= $obj_year->generateItem() . ' ' . $obj_month->generateItem() . ' ' . $obj_day->generateItem();
                }
        $obj_out->setContent($s_content);
        
        return $obj_out->generateItem();
    }

    /**
     * Generates the javascript code
     *
     * @return string javascript code
     */
    private function generateJS()
    {
        $link = $this->html->javascriptLink(NIV . 'js/widgets/birthdayForm.js');
        
        $s_javascript = 'var ' . $this->s_className . ';
    	if( typeof BirthdayForm !== "function" ){
    		$("head").append(\''.$link->generateItem().'\');
    		window.setTimeout(function(){
    			' . $this->s_className . ' = new BirthdayForm();
    			' . $this->s_className . '.init("' . $this->s_dayID . '","' . $this->s_monthID . '","' . $this->s_yearID . '","' . $this->s_callback . '"); 	
    		},1000);
    	}
    	else {
    		' . $this->s_className . ' = new BirthdayForm();
    		' . $this->s_className . '.init("' . $this->s_dayID . '","' . $this->s_monthID . '","' . $this->s_yearID . '","' . $this->s_callback . '");
    	}';
        
        $javascript = $this->html->javascript($s_javascript);
        return $javascript->generateItem();
    }
}