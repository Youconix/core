<?php
namespace youconix\Core\Helpers;

/**
 * General helper interface
 *
 * @author Rachelle Scheijen
 * @since 1.0
 */
abstract class AbstractHelper extends \youconix\core\AbstractObject
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