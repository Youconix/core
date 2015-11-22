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
 * Service class for handling GET and POST requests to external sites
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @version 1.0
 * @since 1.0
 */
class CurlManager extends Service
{

    private $i_timeout = 4000;

    private $s_header;
    
    private $i_headerSize;

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
     * Sets the timeout with the given value in miliseconds.
     * Default value is 4000
     *
     * @param int $i_timeout
     *            The timeout in miliseconds
     */
    public function setTimeout($i_timeout)
    {
        \core\Memory::type('int', $i_timeout);
        
        if ($i_timeout > 0) {
            $this->i_timeout = $i_timeout;
        }
    }

    /**
     * Returns the set timeout
     *
     * @return int The timeout
     */
    public function getTimeout()
    {
        return $this->i_timeout;
    }

    /**
     * Performs a GET call
     *
     * @param String $s_url
     *            The URI to call
     * @param array $a_params
     *            The parameters to add to the URI
     * @return String The content of the called URI
     */
    public function performGetCall($s_url, $a_params)
    {
        $s_url = $this->prepareUrl($s_url, $a_params);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $s_url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // allow redirects
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->i_timeout); // times out after 4s
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Expect:'
        ));
        
        $s_result = curl_exec($ch); // run the whole process
        $this->s_header = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->i_headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        
        if ($this->s_header == 0) {
            $this->s_header = 404;
        }
        
        curl_close($ch);
        
        return $s_result;
    }

    /**
     * Performs a POST call
     *
     * @param String $s_url
     *            The URI to call
     * @param array $a_params
     *            The parameters to add to the URI
     * @param array $a_body
     *            The body to send
     * @return String The content of the called URI
     */
    public function performPostCall($s_url, $a_params, $a_body)
    {
        $s_url = $this->prepareUrl($s_url, $a_params);
        $s_body = $this->prepareBody($a_body);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $s_url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // allow redirects
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->i_timeout); // times out after 4s
        curl_setopt($ch, CURLOPT_POST, 1); // set POST method
        curl_setopt($ch, CURLOPT_POSTFIELDS, $s_body); // add POST fields
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Expect:'
        ));
        
        $s_result = curl_exec($ch); // run the whole process
        $this->s_header = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->i_headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        if ($this->s_header == 0) {
            $this->s_header = 404;
        }
        
        curl_close($ch);
        
        return $s_result;
    }

    /**
     * Prepares the parameters for sending
     *
     * @param String $s_url
     *            The URI to call
     * @param array $a_params
     *            The parameters to add to the URI
     * @return String The prepared URI
     */
    private function prepareUrl($s_url, $a_params)
    {
        if (count($a_params) > 0) {
            $s_params = '';
            $a_keys = array_keys($a_params);
            $i_number = count($a_keys);
            for ($i = 0; $i < $i_number; $i ++) {
                if ($s_params != '') {
                    $s_params . ' &';
                }
                
                $s_params .= $a_keys[$i] . '=' . $a_params[$a_keys[$i]];
            }
            
            $s_url .= '?' . $s_params;
        }
        
        return $s_url;
    }

    /**
     * Prepares the body for sending
     *
     * @param array $a_body
     *            The body to send
     * @return String The prepared body
     */
    private function prepareBody($a_body)
    {
        if ($a_body == array()) {
            return '';
        }
        
        if (count($a_body) == 1) {
            /* check for JSON */
            if (substr($a_body[0], 0, 2) == '[{' && substr($a_body[0], (strlen($a_body[0]) - 2), 2) == '}]') {
                return 'JSON=' . $a_body[0];
            }
        }
        
        if (count($a_body) > 0) {
            $s_body = '';
            
            $a_keys = array_keys($a_body);
            $i_number = count($a_keys);
            for ($i = 0; $i < $i_number; $i ++) {
                if ($s_body != '') {
                    $s_body . ' &';
                }
                
                $s_body .= $a_keys[$i] . '=' . $a_body[$a_keys[$i]];
            }
            
            return $s_body;
        }
    }

    /**
     * Returns the response header
     *
     * @return String The response header
     */
    public function getHeader()
    {
        return $this->s_header;
    }
    
    public function getHeaderSize(){
    	return $this->i_headerSize;
    }
}