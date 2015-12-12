<?php
namespace core\services;

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
 * File-data handler for collecting file specific data
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @version 1.0
 * @since 1.0
 */
class FileData extends Service
{

    /**
     * Returns if the object schould be treated as singleton
     *
     * @return boolean True if the object is a singleton
     */
    public static function isSingleton()
    {
        return true;
    }

    /**
     * Returns the mine-type from the given file.
     * Needs mime_content_type() of finfo_open() on the server to work.
     *
     * @param String $s_file
     *            name
     * @param Boolean $bo_removeDetails
     *            false te preserve the details after ;
     * @return String mine-type
     */
    public function getMimeType($s_file, $bo_removeDetails = true)
    {
        \core\Memory::type('string', $s_file);
        
        if (function_exists('finfo_open')) {
            $s_finfo = finfo_open(FILEINFO_MIME);
            $s_mimeType = finfo_file($s_finfo, $s_file);
            finfo_close($s_finfo);
        } else 
            if (function_exists('mime_content_type')) {
                $s_mimeType = mime_content_type($s_file);
            } else {
                $s_mimeType = "unknown/unknown";
            }
        
        if ($bo_removeDetails && ($i_pos = strpos($s_mimeType, ';')) !== false)
            $s_mimeType = substr($s_mimeType, 0, $i_pos);
        
        return $s_mimeType;
    }

    /**
     * Return the size from the given file.
     * Needs file_size() or stat() on the server to work.
     *
     * @param String $s_file
     *            name
     * @return int size or -1 if the size could not be collected
     */
    public function getFileSize($s_file)
    {
        \core\Memory::type('string', $s_file);
        
        if (function_exists('file_size')) {
            return file_size($s_file);
        } else 
            if (function_exists('stat')) {
                $a_stat = stat($s_file);
                
                return $a_stat[7];
            } else {
                return - 1;
            }
    }

    /**
     * Returns the last date that the given file was accessed.
     * Needs stat() on the server to work.
     *
     * @param String $s_file
     *            name
     * @return int last access date or -1 if the date could not be collected
     */
    public function getLastAccess($s_file)
    {
        \core\Memory::type('string', $s_file);
        
        if (function_exists('stat')) {
            $a_stats = stat($s_file);
            
            return $a_stats[8];
        } else {
            return - 1;
        }
    }

    /**
     * Returns the last data that the given file was modified.
     * Needs stat() on the server to work.
     *
     * @return int last change date or -1 if the date could not be collected
     */
    public function getLastModified($s_file)
    {
        \core\Memory::type('string', $s_file);
        
        if (function_exists('stat')) {
            $a_stats = stat($s_file);
            
            return $a_stats[9];
        } else {
            return - 1;
        }
    }
}