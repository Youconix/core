<?php
namespace core\helpers;

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
 * General helper interface
 *
 * This file is part of Miniature-happines
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
 */
abstract class Helper extends \core\Object
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

interface Display
{

    /**
     * Generates the HTML code
     *
     * @return string HTML code
     */
    public function generate();
}