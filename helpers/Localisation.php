<?php

namespace youconix\core\helpers;

class Localisation extends \youconix\core\helpers\Helper
{

    public function dateTime($timestamp)
    {
        
    }

    /**
     * 
     * @param int|\DateTime $timestamp
     * @return string
     */
    public function dateOrTime($timestamp)
    {
        if ($timestamp instanceof \DateTime) {
            $timestamp = $timestamp->getTimestamp();
        }

        $now = $this->now();
        if (($now - $timestamp) < 86400) {
            return date('H:i:s', $timestamp);
        } else {
            return date('d-m-Y', $timestamp);
        }
    }

    /**
     * 
     * @return int
     */
    public function now()
    {
        return time();
    }

}
