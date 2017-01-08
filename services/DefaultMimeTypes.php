<?php

namespace youconix\core\services;

/**
 * File upload service
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @version 1.0
 * @since 2.0
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
class DefaultMimeTypes extends Service
{
  protected $a_extensions = [];

  public function __construct()
  {
    $this->setExtensions();
  }

  protected function setExtensions()
  {
    $this->a_extensions['csv'] = ['text/comma-separated-values', 'text/csv', 'text/plain',
        'application/csv', 'application/excel', 'application/vnd.ms-excel', 'application/vnd.msexcel',
        'text/anytext'];
  }

  public function __get($s_extension)
  {
    if (!array_key_exists($s_extension, $this->a_extensions)) {
      return 'unkown/unknown';
    }

    return $this->a_extensions[$s_extension];
  }
}