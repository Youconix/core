<?php
namespace core\helpers;

/**
 * Helper for date manipulating
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
 *       
 *        Miniature-happiness is free software: you can redistribute it and/or modify
 *        it under the terms of the GNU Lesser General Public License as published by
 *        the Free Software Foundation, either version 3 of the License, or
 *        (at your option) any later version.
 *       
 *        Miniature-happiness is distributed in the hope that it will be useful,
 *        but WITHOUT ANY WARRANTY; without even the implied warranty of
 *        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *        GNU General Public License for more details.
 *       
 *        You should have received a copy of the GNU Lesser General Public License
 *        along with Miniature-happiness. If not, see <http://www.gnu.org/licenses/>.
 */
class Date extends Helper
{

    /**
     * Calculates the timestamp from the given parameters
     *
     * @param int $i_hours
     *            The hours
     * @param int $i_minutes
     *            The minutes
     * @param int $i_seconds
     *            The seconds
     * @param int $i_day
     *            The day from the month
     * @param int $i_month
     *            The month
     * @param int $i_year
     *            The year
     * @return int The generated timestamp
     */
    public function getTime($i_hours = -1, $i_minutes = -1, $i_seconds = -1, $i_day = -1, $i_month = -1, $i_year = -1)
    {
        if ($i_hours == - 1)
            $i_hours = date("H");
        if ($i_minutes == - 1)
            $i_minutes = date("i");
        if ($i_seconds == - 1)
            $i_seconds = date("s");
        if ($i_month == - 1)
            $i_month = date("n");
        if ($i_day == - 1)
            $i_day = date("j");
        if ($i_year == - 1)
            $i_year = date("Y");
        
        return mktime($i_hours, $i_minutes, $i_seconds, $i_month, $i_day, $i_year);
    }

    /**
     * Returns the timestamp in the future
     *
     * @param int $i_days
     *            of days
     * @param int $i_months
     *            of months
     * @param int $i_years
     *            of years
     * @param int $i_hours
     *            of hours
     * @param int $i_minutes
     *            of minutes
     * @param int $i_seconds
     *            of seconds
     * @return int date
     */
    public function getFutureTime($i_days = 0, $i_months = 0, $i_years = 0, $i_hours = 0, $i_minutes = 0, $i_seconds = 0)
    {
        return mktime(date("H") + $i_hours, date("i") + $i_minutes, date("s") + $i_seconds, date("n") + $i_months, date("j") + $i_days, date("Y") + $i_years);
    }

    /**
     * Calculates the timestamp from now added with the given seconds
     *
     * @param int $i_seconds
     *            The seconds
     * @return int The generated timestamp
     */
    public function getTimeFrom($i_seconds)
    {
        return $this->now() + $i_seconds;
    }

    /**
     * Returns the number of seconds in a day
     *
     * @return int The seconds
     */
    public function getDaySeconds()
    {
        return 86400;
    }

    /**
     * Returns the number of seconds in a week
     *
     * @return int The seconds
     */
    public function getWeekSeconds()
    {
        return $this->getDaySeconds() * 7;
    }

    /**
     * Returns the current timestamp
     *
     * @return int The timestamp
     */
    public function now()
    {
        return time();
    }

    /**
     * Validates the date and time
     *
     * @param int $i_day
     *            day
     * @param int $i_month
     *            month
     * @param int $i_year
     *            year
     * @param int $i_hours
     *            number of hours
     * @param int $i_minutes
     *            number of minutes
     * @param int $i_seconds
     *            number of seconds
     * @return boolean if the date and time is valid
     */
    public function validateDateTime($i_day, $i_month, $i_year, $i_hours, $i_minutes, $i_seconds)
    {
        if (! $this->validateTime($i_hours, $i_minutes, $i_seconds) || ! $this->validateDate($i_day, $i_month, $i_year))
            return false;
        
        return true;
    }

    /**
     * Validates ttime
     *
     * @param int $i_hours
     *            number of hours
     * @param int $i_minutes
     *            number of minutes
     * @param int $i_seconds
     *            number of seconds
     * @return boolean if the time is valid
     */
    public function validateTime($i_hours, $i_minutes, $i_seconds)
    {
        if ($i_hours < 0 || $i_hours > 23 || $i_minutes < 0 || $i_minutes > 59 || $i_seconds < 0 || $i_seconds > 59)
            return false;
        
        return true;
    }

    /**
     * Validates the date
     *
     * @param int $i_day
     *            day
     * @param int $i_month
     *            month
     * @param int $i_year
     *            year
     * @return boolean if the date is valid
     */
    public function validateDate($i_day, $i_month, $i_year)
    {
        if (! is_int($i_day) || ! is_int($i_month) || ! is_int($i_year))
            return false;
        if ($i_month < 1 || $i_month > 12)
            return false;
        
        $i_days = $this->getDaysMonth($i_month, $i_year);
        if ($i_day < 1 || $i_day > $i_days)
            return false;
        
        return true;
    }

    /**
     * Returns the number of days from the given month
     *
     * @param int $i_month
     *            month
     * @param int $i_year
     *            year
     * @return int number of days
     */
    public function getDaysMonth($i_month, $i_year)
    {
        switch ($i_month) {
            case 1:
            case 3:
            case 5:
            case 7:
            case 8:
            case 10:
            case 12:
                return 31;
            
            case 4:
            case 6:
            case 9:
            case 11:
                return 30;
            
            case 2:
                if ($i_year % 400 == 0)
                    return 29;
                if ($i_year % 100 == 0)
                    return 28;
                if ($i_year % 4 == 0)
                    return 29;
                return 28;
        }
    }
}