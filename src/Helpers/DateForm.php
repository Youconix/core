<?php
namespace youconix\core\helpers;

/**
 * Date for generator
 * Generates a date selection lists
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
 */
class DateForm extends Helper
{

    /**
     *
     * @var \youconix\core\helpers\HTML
     */
    protected $html;

    protected $i_startYear;

    protected $i_endYear;

    protected $i_daySelected;

    protected $i_monthSelected;

    protected $i_yearSelected;

    protected $s_scheme;

    /**
     * PHP 5 constructor
     *
     * @param \youconix\core\helpers\html\HTML $HTML
     */
    public function __construct(\youconix\core\helpers\html\HTML $HTML)
    {
        $this->html = $HTML;
        
        $this->reset();
    }

    /**
     * Resets the form
     */
    public function reset()
    {
        $this->i_startYear = date('Y');
        $this->i_endYear = $this->i_startYear + 2;
        
        $this->i_daySelected = - 1;
        $this->i_monthSelected = - 1;
        $this->i_yearSelected = - 1;
        $this->s_scheme = 'd-m-y';
    }

    /**
     * Sets the date scheme :
     * d-m-y (dd-mm-yyyy)
     * m-d-y (mm-dd-yyyy)
     * y-m-d (yyyy-mm-dd)
     *
     * The default is d-m-y
     *
     * @param string $s_scheme
     *            scheme
     */
    public function setScheme($s_scheme)
    {
        if (in_array($s_scheme, array(
            'd-m-y',
            'm-d-y',
            'y-m-d'
        ))) {
            $this->s_scheme = $s_scheme;
        }
    }

    /**
     * Sets the end year
     *
     * @param int $i_year
     *            year
     * @throws \DateException the end year is lower then the start year
     */
    public function setEndYear($i_year)
    {
        if ($i_year < $this->i_startYear)
            throw new \DateException("Selecting year " . $i_year . " before start year " . $this->i_startYear . ".");
        
        $this->i_endYear = $i_year;
    }

    /**
     * Sets the start year
     *
     * @param int $i_year
     *            year
     * @throws \DateException the start year is higer then the end year
     */
    public function setStartYear($i_year)
    {
        if ($i_year > $this->i_endYear)
            throw new \DateException("Selecting year " . $i_year . " after end year " . $this->i_startYear . ".");
        
        $this->i_startYear = $i_year;
    }

    /**
     * Sets the selected date
     * All the fields can be set on -1 for no value
     *
     * @param int $i_day
     *            day
     * @param int $i_month
     *            month
     * @param int $i_year
     *            year
     * @throws \DateException the day or month is invalid
     */
    public function setSelected($i_day, $i_month, $i_year)
    {
        if ($i_day != - 1 && ($i_day < 1 || $i_day > 31))
            throw new \DateException('Selecting invalid day ' . $i_day);
        
        if ($i_month != - 1 && ($i_month < 1 || $i_month > 12))
            throw new \DateException('Selecting invalid month ' . $i_month);
        
        if ($i_year != - 1 && ($i_year < $this->i_startYear || $i_year > $this->i_endYear))
            throw new \DateException('Selecting invalid year ' . $i_year);
        
        $this->i_daySelected = $i_day;
        $this->i_monthSelected = $i_month;
        $this->i_yearSelected = $i_year;
    }

    /**
     * Generates the form
     *
     * @param string $s_idDay
     *            day form name, default day
     * @param string $s_idMonth
     *            month form name, default month
     * @param string $s_idYear
     *            year form name, default year
     * @return string generated form
     */
    public function generate($s_idDay = 'day', $s_idMonth = 'month', $s_idYear = 'year')
    {
        /* Generate day list */
        $obj_day = $this->generateList(1, 31, $this->i_daySelected, $s_idDay);
        
        /* Generate month list */
        $obj_month = $this->generateList(1, 12, $this->i_monthSelected, $s_idMonth);
        
        /* Generate year list */
        $obj_year = $this->generateList($this->i_startYear, $this->i_endYear, $this->i_yearSelected, $s_idYear);
        
        /* Generate widget */
        $obj_out = $this->html->div();
        $obj_out->setClass('dateForm');
        
        if ($this->s_scheme == 'd-m-y') {
            $obj_out->setContent($obj_day->generateItem() . ' ' . $obj_month->generateItem() . ' ' . $obj_year->generateItem());
        } else 
            if ($this->s_scheme == 'm-d-y') {
                $obj_out->setContent($obj_month->generateItem() . ' ' . $obj_day->generateItem() . ' ' . $obj_year->generateItem());
            } else 
                if ($this->s_scheme == 'y-m-d') {
                    $obj_out->setContent($obj_year->generateItem() . ' ' . $obj_month->generateItem() . ' ' . $obj_day->generateItem());
                }
        
        return $obj_out->generateItem();
    }

    /**
     * Returns the year list
     *
     * @param int $i_start
     *            start year
     * @param int $i_end
     *            end year
     * @param int $i_selected
     *            selected year
     * @param string $s_id
     *            list name
     * @return string The list
     *        
     */
    public function createYearList($i_startYear, $i_endYear, $i_selected, $s_id)
    {
        return $this->generateList($i_startYear, $i_endYear, $i_selected, $s_id);
    }

    /**
     * Generates a selection list
     *
     * @param int $i_start
     *            start value
     * @param int $i_end
     *            end value
     * @param int $i_selected
     *            selected value
     * @param string $s_id
     *            form name
     * @return \youconix\core\helpers\html\Select selection list
     */
    protected function generateList($i_start, $i_end, $i_selected, $s_id)
    {
        $obj_item = $this->html->select($s_id)->setID($s_id);
        if ($i_selected == - 1) {
            $i_selected = $i_start;
        }
        
        for ($i = $i_start; $i <= $i_end; $i ++) {
            if ($i == $i_selected)
                $obj_item->setOption($i, true);
            else
                $obj_item->setOption($i, false);
        }
        
        return $obj_item;
    }
}