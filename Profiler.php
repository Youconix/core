<?php

class Profiler
{

    private static $i_start;

    private static $a_records;

    private static $a_debug;

    public static function reset()
    {
        Profiler::$a_records = array();
        Profiler::$a_debug = array();
        Profiler::$i_start = microtime(true);
    }

    public static function profileSystem($s_file, $s_text)
    {
        array_push(Profiler::$a_records, array(
            $s_file,
            $s_text,
            round((microtime(true) - Profiler::$i_start), 5)
        ));
    }

    public static function profile($s_file, $s_text)
    {
        array_push(Profiler::$a_records, array(
            $s_file,
            $s_text,
            round((microtime(true) - Profiler::$i_start), 5)
        ));
    }

    public static function dump()
    {
       
    }
}
