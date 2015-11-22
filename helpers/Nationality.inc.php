<?php

/**
 * Nationality list widget
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
class Helper_Nationality extends Helper
{

    private $a_nationalities;

    /**
     * Creates the nationality helper
     * 
     * @param \Builder	$builder	The query builder
     */
    public function __construct(\Builder $builder)
    {
        $builder->select("nationalities", "*");
        $service_Database = $builder->getResult();
        if ($service_Database->num_rows() > 0) {
            $a_data = $service_Database->fetch_assoc_key('nationality');
            ksort($a_data, SORT_STRING);
            
            foreach ($a_data as $a_item) {
                $this->a_nationalities[$a_item['id']] = $a_item;
            }
        }
    }

    /**
     * Returns the nationality
     *
     * @param int $i_id
     *            nationality ID
     * @return array nationality
     * @throws \OutOfBoundsException the ID does not exist
     */
    public function getItem($i_id)
    {
        if (! array_key_exists($i_id, $this->a_nationalities)) {
            throw new \OutOfBoundsException("Call to unknown nationality with id " . $i_id . '.');
        }
        
        return $this->a_nationalities[$i_id];
    }

    /**
     * Returns the nationalities sorted on name
     *
     * @return array The nationalities
     */
    public function getItems()
    {
        return $this->a_nationalities;
    }

    /**
     * Generates the selection list
     *
     * @param string $s_field
     *            list name
     * @param string $s_id
     *            list id
     * @param string $s_default
     *            default value, optional
     * @return string list
     */
    public function getList($s_field, $s_id, $i_default)
    {
        $obj_Select = Memory::helpers('HTML')->select($s_field);
        $obj_Select->setID($s_id);
        
        foreach ($this->a_nationalities as $a_nationality) {
            ($a_nationality['id'] == $i_default) ? $bo_selected = true : $bo_selected = false;
            
            $obj_Select->setOption($a_nationality['nationality'], $bo_selected, $a_nationality['id']);
        }
        
        return $obj_Select->generateItem();
    }

    /**
     * Returns the country telephone codes
     *
     * @return array The codes
     */
    public function getCountryTelephoneCodes()
    {
        $a_codes = array(
            '93',
            '213',
            '355',
            '1684',
            '358 18',
            '7840',
            '7940',
            '99544',
            '376',
            '244',
            '1264',
            '1268',
            '54',
            '374',
            '297',
            '247',
            '61',
            '672',
            '43',
            '994',
            '1242',
            '973',
            '880',
            '1246',
            '1268',
            '375',
            '32',
            '501',
            '229',
            '1441',
            '975',
            '591',
            '5997',
            '387',
            '267',
            '55',
            '246',
            '1284',
            '673',
            '359',
            '226',
            '95',
            '257',
            '855',
            '237',
            '1',
            '238',
            '5993',
            '5994',
            '5997',
            '1345',
            '236',
            '235',
            '64',
            '56',
            '86',
            '61',
            '61',
            '57',
            '269',
            '242',
            '243',
            '682',
            '506',
            '225',
            '385',
            '53',
            '53 99',
            '5999',
            '357',
            '420',
            '45',
            '246',
            '253',
            '1767',
            '1809',
            '1829',
            '1849',
            '670',
            '56',
            '593',
            '20',
            '503',
            '8812',
            '8813',
            '88213',
            '240',
            '291',
            '372',
            '251',
            '500',
            '298',
            '679',
            '358',
            '33',
            '596',
            '594',
            '689',
            '241',
            '220',
            '995',
            '49',
            '233',
            '350',
            '881',
            '8818',
            '8819',
            '30',
            '299',
            '1473',
            '590',
            '1671',
            '502',
            '44',
            '224',
            '245',
            '592',
            '509',
            '504',
            '852',
            '36',
            '354',
            '8810',
            '8811',
            '91',
            '62',
            '870',
            '800',
            '808',
            '98',
            '964',
            '353',
            '8816',
            '8817',
            '44',
            '972',
            '39',
            '1876',
            '4779',
            '81',
            '44',
            '962',
            '7 6',
            '77',
            '254',
            '686',
            '850',
            '82',
            '965',
            '996',
            '856',
            '371',
            '961',
            '266',
            '231',
            '218',
            '423',
            '370',
            '352',
            '853',
            '389',
            '261',
            '265',
            '60',
            '960',
            '223',
            '356',
            '692',
            '596',
            '222',
            '230',
            '262',
            '52',
            '691',
            '1808',
            '373',
            '377',
            '976',
            '382',
            '1664',
            '212',
            '258',
            '264',
            '674',
            '977',
            '31',
            '1869',
            '687',
            '64',
            '505',
            '227',
            '234',
            '683',
            '672',
            '1670',
            '47',
            '968',
            '92',
            '680',
            '970',
            '507',
            '675',
            '595',
            '51',
            '63',
            '64',
            '48',
            '351',
            '1787',
            '1939',
            '974',
            '262',
            '40',
            '7',
            '250',
            '599',
            '590',
            '290',
            '1869',
            '1758',
            '590',
            '508',
            '1784',
            '685',
            '378',
            '239',
            '966',
            '221',
            '381',
            '248',
            '232',
            '65',
            '599 3',
            '1721',
            '421',
            '386',
            '677',
            '252',
            '27',
            '500',
            '99534',
            '211',
            '34',
            '94',
            '249',
            '597',
            '47 79',
            '268',
            '46',
            '41',
            '963',
            '886',
            '992',
            '255',
            '66',
            '88216',
            '228',
            '690',
            '676',
            '1868',
            '2908',
            '216',
            '90',
            '993',
            '1649',
            '688',
            '256',
            '380',
            '971',
            '44',
            '878',
            '598',
            '1340',
            '998',
            '678',
            '58',
            '39066',
            '379',
            '84',
            '1808',
            '681',
            '967',
            '260',
            '255',
            '263'
        );
        
        sort($a_codes);
        
        return $a_codes;
    }
}