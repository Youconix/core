<?php
namespace core\models;

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
 * Stats model.
 * Contains the site statistics
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
 */
class Stats extends Model
{

    private $i_date;

    /**
     * PHP5 constructor
     *
     * @param \Builder $builder
     * @param \core\services\Validation $validation
     */
    public function __construct(\Builder $builder, \core\services\Validation $validation)
    {
        parent::__construct($builder, $validation);
        
        $this->i_date = mktime(0, 0, 0, date("n"), 1, date("Y"));
    }

    /**
     * Counts the visitor hit
     *
     * @param string $s_ip
     *            The IP address
     * @param string $s_page
     *            current page
     * @return Boolean True if the visitor is unique, otherwise false
     */
    public function saveIP($s_ip, $s_page)
    {
        \core\Memory::type('string', $s_ip);
        \core\Memory::type('string', $s_page);
        
        $i_datetime = mktime(date("H"), 0, 0, date("n"), date('j'), date("Y"));
        $this->builder->update('stats_hits', 'amount', 'l', 'amount + 1')
            ->getWhere()
            ->addAnd('datetime', 'i', $i_datetime);
        
        if ($this->builder->getResult()->affected_rows() == 0) {
            $this->builder->insert('stats_hits', array(
                'amount',
                'datetime'
            ), array(
                'i',
                'i'
            ), array(
                1,
                $i_datetime
            ))->getResult();
        }
        
        $this->builder->update('stats_pages', 'amount', 'l', 'amount = amount + 1');
        $this->builder->getWhere()->addAnd(array(
            'datetime',
            'name'
        ), array(
            'i',
            's'
        ), array(
            $i_datetime,
            $s_page
        ));
        $service_Database = $this->builder->getResult();
        
        if ($service_Database->affected_rows() == 0) {
            $this->builder->insert('stats_pages', array(
                'amount',
                'datetime',
                'name'
            ), array(
                'i',
                'i',
                's'
            ), array(
                1,
                $i_datetime,
                $s_page
            ))->getResult();
        }
        
        /* Check unique */
        $i_begin = mktime(0, 0, 0, date("n"), date('j'), date("Y"));
        $i_end = mktime(23, 59, 59, date("n"), date('j'), date("Y"));
        $this->builder->select('stats_unique', 'id')
            ->getWhere()
            ->addAnd(array(
            'ip',
            'datetime'
        ), array(
            's',
            'i',
            'i'
        ), array(
            $s_ip,
            $i_begin,
            $i_end
        ), array(
            '=',
            'BETWEEN'
        ));
        
        $service_Database = $this->builder->getResult();
        if ($service_Database->num_rows() != 0) {
            return false;
        }
        
        /* Unique visitor */
        $this->builder->insert('stats_unique', array(
            'ip',
            'datetime'
        ), array(
            's',
            'i'
        ), array(
            $s_ip,
            time()
        ))->getResult();
        
        return true;
    }

    /**
     * Saves the visitors OS
     *
     * @param string $s_os
     *            The OS
     * @param string $s_osType
     *            OS family
     */
    public function saveOS($s_os, $s_osType)
    {
        \core\Memory::type('string', $s_os);
        \core\Memory::type('string', $s_osType);
        
        $this->builder->update('stats_OS', 'amount', 'l', 'amount + 1')
            ->getWhere()
            ->addAnd(array(
            'datetime',
            'name',
            'type'
        ), array(
            'i',
            's',
            's'
        ), array(
            $this->i_date,
            $s_os,
            $s_osType
        ));
        $service_Database = $this->builder->getResult();
        
        if ($service_Database->affected_rows() == 0) {
            $this->builder->insert('stats_OS', array(
                'name',
                'amount',
                'datetime',
                'type'
            ), array(
                's',
                'i',
                'i',
                's'
            ), array(
                $s_os,
                1,
                $this->i_date,
                $s_osType
            ))->getResult();
        }
    }

    /**
     * Saves the visitors browser
     *
     * @param string $s_browser
     *            The browser
     * @param string $s_version
     *            browser version
     */
    public function saveBrowser($s_browser, $s_version)
    {
        \core\Memory::type('string', $s_browser);
        \core\Memory::type('string', $s_version);
        
        $this->builder->update('stats_browser', 'amount', 'l', 'amount + 1')
            ->getWhere()
            ->addAnd(array(
            'datetime',
            'name',
            'version'
        ), array(
            'i',
            's',
            's'
        ), array(
            $this->i_date,
            $s_browser,
            $s_version
        ));
        
        $service_Database = $this->builder->getResult();
        if ($service_Database->affected_rows() == 0) {
            $this->builder->insert('stats_browser', array(
                'name',
                'amount',
                'datetime',
                'version'
            ), array(
                's',
                'i',
                'i',
                's'
            ), array(
                $s_browser,
                1,
                $this->i_date,
                $s_version
            ))->getResult();
        }
    }

    /**
     * Saves the visitors reference
     *
     * @param string $s_reference
     *            The reference
     */
    public function saveReference($s_reference)
    {
        \core\Memory::type('string', $s_reference);
        
        $s_reference = str_replace(array(
            '\\',
            'http://',
            'https://'
        ), array(
            '/',
            '',
            ''
        ), $s_reference);
        $a_reference = explode('/', $s_reference);
        $s_reference = $a_reference[0];
        
        $this->builder->update('stats_reference', 'amount', 'l', 'amount + 1')
            ->getWhere()
            ->addAnd(array(
            'datetime',
            'name'
        ), array(
            'i',
            's'
        ), array(
            $this->i_date,
            $s_reference
        ));
        $service_Database = $this->builder->getResult();
        
        if ($service_Database->affected_rows() == 0) {
            $this->builder->insert('stats_reference', array(
                'name',
                'amount',
                'datetime'
            ), array(
                's',
                'i',
                'i'
            ), array(
                $s_reference,
                1,
                $this->i_date
            ))->getResult();
        }
    }

    /**
     * Saves the visitors screen size
     *
     * @param int $i_width
     *            width
     * @param int $i_height
     *            height
     */
    public function saveScreenSize($i_width, $i_height)
    {
        \core\Memory::type('int', $i_width);
        \core\Memory::type('int', $i_height);
        
        $this->builder->update('stats_screenSizes', 'amount', 'l', 'amount + 1')
            ->getWhere()
            ->addAnd(array(
            'datetime',
            'width',
            'height'
        ), array(
            'i',
            'i',
            'i'
        ), array(
            $this->i_date,
            $i_width,
            $i_height
        ));
        $service_Database = $this->builder->getResult();
        
        if ($service_Database->affected_rows() == 0) {
            $this->builder->insert('stats_screenSizes', array(
                'width',
                'height',
                'amount',
                'datetime'
            ), array(
                'i',
                'i',
                'i',
                'i'
            ), array(
                $i_width,
                $i_height,
                1,
                $this->i_date
            ))->getResult();
        }
    }

    /**
     * Saves the visitors screen colors
     *
     * @param string $s_screenColors
     *            The screen colors
     */
    public function saveScreenColors($s_screenColors)
    {
        \core\Memory::type('string', $s_screenColors);
        
        $this->builder->update('stats_screenColors', 'amount', 'l', 'amount + 1')
            ->getWhere()
            ->addAnd(array(
            'datetime',
            'name'
        ), array(
            'i',
            's'
        ), array(
            $this->i_date,
            $s_screenColors
        ));
        $service_Database = $this->builder->getResult();
        
        if ($service_Database->affected_rows() == 0) {
            $this->builder->insert('stats_screenColors', array(
                'name',
                'amount',
                'datetime'
            ), array(
                's',
                'i',
                'i'
            ), array(
                $s_screenColors,
                1,
                $this->i_date
            ))->getResult();
        }
    }

    /**
     * Returns the hits pro month
     *
     * @param int   $i_startDate    The start date as timestamp
     * @param int   $i_endDate      The end date as timestamp
     * @return \core\models\data\HitCollection The hits
     */
    public function getHits($i_startDate,$i_endDate)
    {
        \core\Memory::type('int', $i_startDate);
        \core\Memory::type('int', $i_endDate);
        
        $hits = new \core\models\data\HitCollection($i_startDate,$i_endDate);
        $this->builder->select('stats_hits', 'amount,datetime')
            ->group('datetime')
            ->getWhere()
            ->addAnd('datetime', array(
            'i',
            'i'
        ), array(
            $i_startDate,
            $i_endDate
        ), 'BETWEEN');
        $service_Database = $this->builder->getResult();
        
        if ($service_Database->num_rows() > 0) {
            $a_hitsPre = $service_Database->fetch_assoc();
            
            foreach ($a_hitsPre as $a_hit) {
                $item = new \core\models\data\HitItem($a_hit['amount'], $a_hit['datetime']);
                $hits->add($item);
            }
        }
        
        return $hits;
    }
    
    /**
     * Returns the unique visitors from the given month
     *
     * @param int   $i_startDate    The start date as timestamp
     * @param int   $i_endDate      The end date as timestamp
     * @return \core\models\data\HitCollection The visitors
     */
    public function getUnique($i_startDate,$i_endDate)
    {
        \core\Memory::type('int', $i_startDate);
        \core\Memory::type('int', $i_endDate);
        
        $unique = new \core\models\data\HitCollection($i_startDate,$i_endDate);
    
        $this->builder->select('stats_unique', 'datetime')
        ->group('datetime')
        ->getWhere()
        ->addAnd('datetime', array(
            'i',
            'i'
        ), array(
            $i_startDate,
            $i_endDate
        ), 'BETWEEN');
        $service_Database = $this->builder->getResult();
    
        if ($service_Database->num_rows() > 0) {
            $a_uniquePre = $service_Database->fetch_assoc();
    
            foreach ($a_uniquePre as $a_hit) {
                $item = new \core\models\data\HitItem(1, $a_hit['datetime']);
                $unique->add($item);
            }
        }
    
        return $unique;
    }
    
    /**
     * Returns the hits pro hour
     * 
     * @param int   $i_startDate    The start date as timestamp
     * @param int   $i_endDate      The end date as timestamp
     * @return array    The hits
     */
    public function getHitsHours($i_startDate,$i_endDate)
    {
        \core\Memory::type('int', $i_startDate);
        \core\Memory::type('int', $i_endDate);
        
        $a_hits = array();
        for( $i=0; $i<=23; $i++){
            $a_hits[$i] = 0;
        }
        
        $this->builder->select('stats_hits', 'amount,datetime')
        ->group('datetime')
        ->getWhere()
        ->addAnd('datetime', array(
            'i',
            'i'
        ), array(
            $i_startDate,
            $i_endDate
        ), 'BETWEEN');
        $service_Database = $this->builder->getResult();
        
        if ($service_Database->num_rows() > 0) {
            $a_hitsPre = $service_Database->fetch_assoc();
        
            foreach ($a_hitsPre as $a_hit) {
                $a_hits[ date('H',$a_hit['datetime'])] += $a_hit['amount'];
            }
        }
        
        return $a_hits;
    }

    /**
     * Returns the pages
     *
     * @param int   $i_startDate    The start date as timestamp
     * @param int   $i_endDate      The end date as timestamp
     * @return array The pages
     */
    public function getPages($i_startDate,$i_endDate)
    {
        \core\Memory::type('int', $i_startDate);
        \core\Memory::type('int', $i_endDate);
        
        $a_pages = array();
        $this->builder->select('stats_pages', 'name,SUM(amount) AS amount')
            ->group('name')
            ->order('amount', 'DESC');
        $this->builder->getWhere()->addAnd('datetime', array(
            'i',
            'i'
        ), array(
            $i_startDate,
            $i_endDate
        ), 'BETWEEN');
        $service_Database = $this->builder->getResult();
        
        if ($service_Database->num_rows() > 0) {
            $a_pages = $this->service_Database->fetch_assoc();
        }
        
        return $a_pages;
    }
    
    /**
     * Sorts the dates
     * 
     * @param array $a_data	The dates
     * @return array
     */
    private function sortDate($a_data){
        $a_items = array();
        $a_data2 = array();
        foreach( $a_data AS $a_item ){
            if( !array_key_exists($a_item['type'], $a_data2) ){
                $a_data2[ $a_item['type'] ] = array();
            }
        
            $a_data2[ $a_item['type'] ][ str_replace(' ','',$a_item['name']) ] = $a_item;
        }
        
        ksort($a_data2);
        foreach( $a_data2 AS $key => $item ){
            ksort($a_data2[$key]);
        }
        
        
        foreach( $a_data2 AS $key => $type ){
            foreach( $a_data2[$key] AS $item ){
                $a_items[] = $item;
            }
        }
        
        return $a_items;
    }

    /**
     * Returns the operating systems
     *
     * @param int   $i_startDate    The start date as timestamp
     * @param int   $i_endDate      The end date as timestamp
     * @return array The operating systems
     */
    public function getOS($i_startDate,$i_endDate)
    {
        \core\Memory::type('int', $i_startDate);
        \core\Memory::type('int', $i_endDate);
        
        $a_OS = array();
        $this->builder->select('stats_OS', 'id,name,amount,type')
            ->getWhere()
            ->addAnd('datetime', array(
            'i',
            'i'
        ), array(
            $i_startDate,
            $i_endDate
        ), 'BETWEEN');
        $service_Database = $this->builder->getResult();
        
        if ($service_Database->num_rows() > 0) {
            $a_data = $service_Database->fetch_assoc();
            
            $a_OS = $this->sortDate($a_data);
        }
                
        return $a_OS;
    }

    /**
     * Returns the browsers
     * Grouped by browser
     *
     * @param int   $i_startDate    The start date as timestamp
     * @param int   $i_endDate      The end date as timestamp
     * @return array The browsers
     */
    public function getBrowsers($i_startDate,$i_endDate)
    {
        \core\Memory::type('int', $i_startDate);
        \core\Memory::type('int', $i_endDate);
        
        $a_browsers = array();
        $this->builder->select('stats_browser', 'id,name AS type,amount,CONCAT(name," ",version) AS name')
            ->group('name')
            ->order('amount', 'DESC');
        $this->builder->getWhere()->addAnd('datetime', array(
            'i',
            'i'
        ), array(
            $i_startDate,
            $i_endDate
        ), 'BETWEEN');
        $service_Database = $this->builder->getResult();
        
        if ($service_Database->num_rows() > 0) {
            $a_data = $service_Database->fetch_assoc_key('name');
            $a_browsers = $this->sortDate($a_data);
        }
        
        return $a_browsers;
    }

    /**
     * Returns the screen colors
     *
     * @param int   $i_startDate    The start date as timestamp
     * @param int   $i_endDate      The end date as timestamp
     * @return array The screen colors
     */
    public function getScreenColors($i_startDate,$i_endDate)
    {
        \core\Memory::type('int', $i_startDate);
        \core\Memory::type('int', $i_endDate);
        
        $a_screenColors = array();
        $this->builder->select('stats_screenColors', 'name,amount')
            ->getWhere()
            ->addAnd('datetime', array(
            'i',
            'i'
        ), array(
            $i_startDate,
            $i_endDate
        ), 'BETWEEN');
        $service_Database = $this->builder->getResult();
        
        if ($service_Database->num_rows() > 0) {
            $a_screenColors = $service_Database->fetch_assoc();
        }
        
        return $a_screenColors;
    }

    /**
     * Returns the screen sizes
     *
     * @param int   $i_startDate    The start date as timestamp
     * @param int   $i_endDate      The end date as timestamp
     * @return array The screen sizes
     */
    public function getScreenSizes($i_startDate,$i_endDate)
    {
        \core\Memory::type('int', $i_startDate);
        \core\Memory::type('int', $i_endDate);
        
        $a_screenSizes = array();
        $this->builder->select('stats_screenSizes', 'width,height,amount')->order('width', 'DESC', 'height', 'DESC');
        $this->builder->getWhere()->addAnd('datetime', array(
            'i',
            'i'
        ), array(
            $i_startDate,
            $i_endDate
        ), 'BETWEEN');
        $service_Database = $this->builder->getResult();
        
        if ($service_Database->num_rows() > 0) {
            $a_screenSizes = $service_Database->fetch_assoc();
        }
        
        return $a_screenSizes;
    }

    /**
     * Returns the references
     *
     * @param int   $i_startDate    The start date as timestamp
     * @param int   $i_endDate      The end date as timestamp
     * @return array The references
     */
    public function getReferences($i_startDate,$i_endDate)
    {
        \core\Memory::type('int', $i_startDate);
        \core\Memory::type('int', $i_endDate);
        
        $a_references = array();
        $this->builder->select('stats_reference','SUM(amount) AS amount,name')
            ->order('amount', 'DESC')
            ->group('name');
        $this->builder->getWhere()->addAnd('datetime', array(
            'i',
            'i'
        ), array(
            $i_startDate,
            $i_endDate
        ), 'BETWEEN');
        $service_Database = $this->builder->getResult();
        
        if ($service_Database->num_rows() > 0) {
            $a_references = $service_Database->fetch_assoc();
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
        $this->builder->select('stats_hits', $this->builder->getMinimun('datetime', 'date'));
        $service_Database = $this->builder->getResult();
        
        if ($service_Database->num_rows() > 0) {
            $i_date = (int) $service_Database->result(0, 'date');
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
        $i_time = mktime(date("H"), date("i"), date("s"), date("n"), date("j"), date("Y") - 1);
        $this->cleanStats($i_time);
    }

    /**
     * Cleans the stats from a month old
     *
     * @throws DBException If the clearing failes
     */
    public function cleanStatsMonth()
    {
        $i_time = mktime(date("H"), date("i"), date("s"), date("n") - 1, date("j"), date("Y"));
        $this->cleanStats($i_time);
    }

    /**
     * Deletes the stats older than the given timestamp
     *
     * @param int $i_maxDate
     *            minimun timestamp to keep data
     * @throws DBException If the clearing failes
     */
    private function cleanStats($i_maxDate)
    {
        \core\Memory::type('int', $i_maxDate);
        
        try {
            $this->builder->transaction();
            
            $this->builder->delete('stats_hits')
                ->getWhere()
                ->addAnd('datetime', 'i', $i_maxDate, '<');
            $this->builder->getResult();
            $this->builder->delete('stats_pages')
                ->getWhere()
                ->addAnd('datetime', 'i', $i_maxDate, '<');
            $this->builder->getResult();
            $this->builder->delete('stats_unique')
                ->getWhere()
                ->addAnd('datetime', 'i', $i_maxDate, '<');
            $this->builder->getResult();
            $this->builder->delete('stats_screenSizes')
                ->getWhere()
                ->addAnd('datetime', 'i', $i_maxDate, '<');
            $this->builder->getResult();
            $this->builder->delete('stats_screenColors')
                ->getWhere()
                ->addAnd('datetime', 'i', $i_maxDate, '<');
            $this->builder->getResult();
            $this->builder->delete('stats_browser')
                ->getWhere()
                ->addAnd('datetime', 'i', $i_maxDate, '<');
            $this->builder->getResult();
            $this->builder->delete('stats_reference')
                ->getWhere()
                ->addAnd('datetime', 'i', $i_maxDate, '<');
            $this->builder->getResult();
            $this->builder->delete('stats_OS')
                ->getWhere()
                ->addAnd('datetime', 'i', $i_maxDate, '<');
            $this->builder->getResult();
            
            $this->builder->commit();
        } catch (\DBException $e) {
            $this->builder->rollback();
            throw $e;
        }
    }
}