<?php
namespace youconix\core\helpers;

/**
 * General helper interface
 *
 * This file is part of Miniature-happines
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
 */
abstract class Helper extends \youconix\core\Object
{

    /**
     * Clones the helper
     *
     * @return The cloned helper
     */
    public function cloneHelper()
    {
        return clone $this;
    }
}