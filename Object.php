<?php
namespace youconix\core;

abstract class Object
{

    /**
     * Returns if the object schould be treated as singleton
     *
     * @return boolean True if the object is a singleton
     */
    public static function isSingleton()
    {
        return false;
    }
}