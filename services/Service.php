<?php
namespace youconix\core\services;

/**
 * Service parent class
 * This class is abstract and should be inheritanced by every service
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @version 1.0
 * @since 1.0
 */
abstract class Service extends \youconix\core\Object
{

    /**
     * Clones the service
     *
     * @return Service clone from the service
     */
    public function cloneService()
    {
        return clone $this;
    }
}