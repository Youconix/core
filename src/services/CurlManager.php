<?php
namespace youconix\core\services;

/**
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

    protected $i_timeout = 4000;

    protected $s_header;
    
    protected $i_headerSize;

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
        \youconix\core\Memory::type('int', $i_timeout);
        
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
     * @param string $s_url
     * @param string $s_destination
     */
    public function download($s_url, $s_destination)
    {
        $file = fopen($s_destination, 'w+');
		
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $s_url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // allow redirects
        curl_setopt($ch, CURLOPT_FILE, $file); 
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->i_timeout); // times out after 4s
        $this->prepareHeaders($ch);
        
        curl_exec($ch);
        $this->s_header = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->i_headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        
        if ($this->s_header == 0) {
            $this->s_header = 404;
        }
        
        curl_close($ch);
		
        $this->a_headers = [];
    }

    /**
     * Performs a GET call
     *
     * @param string $s_url
     *            The URI to call
     * @param array $a_params
     *            The parameters to add to the URI
     * @return string The content of the called URI
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
     * @param string $s_url
     *            The URI to call
     * @param array $a_params
     *            The parameters to add to the URI
     * @param array $a_body
     *            The body to send
     * @return string The content of the called URI
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
     * @param string $s_url
     *            The URI to call
     * @param array $a_params
     *            The parameters to add to the URI
     * @return string The prepared URI
     */
    protected function prepareUrl($s_url, $a_params)
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
     * @return string The prepared body
     */
    protected function prepareBody($a_body)
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
     * @return string The response header
     */
    public function getHeader()
    {
        return $this->s_header;
    }
    
    public function getHeaderSize(){
    	return $this->i_headerSize;
    }
}
