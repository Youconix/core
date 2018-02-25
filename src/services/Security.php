<?php
namespace youconix\core\services;

/**
 * Security class for checking user-input
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @version 1.0
 * @since 1.0
 */
class Security extends Service implements \Security
{

    /**
     *
     * @var \Validation
     */
    protected $validation;

    public function __construct(\Validation $validation)
    {
        $this->validation = $validation;
    }

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
     * Checks for correct boolean value
     *
     * @param boolean $bo_input
     *            The value to check
     * @return boolean checked value
     */
    public function secureBoolean($bo_input)
    {
        if ($bo_input === false || $bo_input === true) {
            return $bo_input;
        }
        if ($bo_input === 0 || $bo_input === 1 || $bo_input === '0' || $bo_input === '1') {
            return $bo_input;
        }
        
        return false;
    }

    /**
     * Checks for correct int value
     *
     * @param int $i_input
     *            The value to check
     * @param boolean $bo_positive
     *            Set to true for positive values only
     * @return int The checked value
     */
    public function secureInt($i_input, $bo_positive = false)
    {
        if (! is_numeric($i_input) || ! preg_match("#^-?[0-9]+$#si", $i_input)) {
            return 0;
        }
        
        if ($bo_positive && $i_input < 0) {
            return 0;
        }
        
        return (int) $i_input;
    }

    /**
     * Checks for correct float value
     *
     * @param float $fl_input
     *            The value to check
     * @param boolean $bo_positive
     *            Set to true for positive values only
     * @return float The checked value
     */
    public function secureFloat($fl_input, $bo_positive = false)
    {
        if (! is_numeric($fl_input) || ! preg_match("#^-?[0-9]+(,|\.)?[0-9]*$#si", $fl_input)) {
            return 0.00;
        }
        
        if ($bo_positive && $fl_input < 0) {
            return 0.00;
        }
        
        return (float) $fl_input;
    }

    /**
     * Disables code in the given string
     *
     * @param string $s_input
     *            The value to make safe
     * @return string The secured value
     */
    public function secureString($s_input)
    {
        $s_input = trim($s_input);
        $s_input = stripslashes($s_input);
        $s_input = htmlentities($s_input, ENT_QUOTES);
        
        /* Disable JavaScript */
        $s_input = preg_replace("#javascript:+[a-zA-Z0-9_]+\(+[a-zA-Z0-9_\-'\",:/\.]*\)+#si", "", $s_input);
        $s_input = str_replace("%3C", "&gt;", $s_input);
        $s_input = str_replace("%3E", "&gt;", $s_input);
        $s_input = preg_replace("#%3C/script%3E#si", "", $s_input);
        
        /* Disbable Javascript-events */
        $s_input = preg_replace('#on+[a-zA-Z]+[[:space:]]*=+[[:space:]]*(\"| \')*[a-zA-Z0-9_\-\.\'\"]+(\"| \')*#si', "", $s_input);
        
        return $s_input;
    }

    /**
     * Disables code in the given string for DB input
     *
     * @param string $s_input
     *            The value to make safe
     * @return string The secured value
     */
    public function secureStringDB($s_input)
    {
        $s_input = $this->secureString($s_input);
        $s_input = str_replace(array(
            '\r\n',
            '\r',
            '\n'
        ), array(
            "\n",
            "\n",
            "\n"
        ), $s_input);
        
        return $s_input;
    }

    /**
     * Validates the given email address
     *
     * @param string $s_email
     *            The email address
     * @deprecated
     *
     * @return boolean True if the email address is valid, otherwise false
     */
    public function checkEmail($s_email)
    {
        if (! \youconix\core\Memory::isTesting()) {
            trigger_error("This function has been deprecated. Please use \youconix\core\services\Validation->checkEmail() instead.", E_USER_DEPRECATED);
        }
        return $this->validation->checkEmail($s_email);
    }

    /**
     * Validates the given URI
     *
     * @param string $s_uri
     *            The URI
     * @deprecated
     *
     * @return boolean True if the URI is valid, otherwise false
     */
    public function checkURI($s_uri)
    {
        if (! \youconix\core\Memory::isTesting()) {
            trigger_error("This function has been deprecated. Please use \youconix\core\services\Validation->checkURI() instead.", E_USER_DEPRECATED);
        }
        return $this->validation->checkURI($s_uri);
    }

    /**
     * Validates the given dutch postal address
     *
     * @param string $s_value
     *            The postal address
     * @deprecated
     *
     * @return boolean True if the postal address is valid, otherwise false
     */
    public function checkPostalNL($s_value)
    {
        if (! \youconix\core\Memory::isTesting()) {
            trigger_error("This function has been deprecated. Please use \youconix\core\services\Validation->checkPostalNL() instead.", E_USER_DEPRECATED);
        }
        return $this->validation->checkPostalNL($s_value);
    }

    /**
     * Validates the given belgium postal address
     *
     * @param string $s_value
     *            The postal address
     * @deprecated
     *
     * @return boolean True if the postal address is valid, otherwise false
     */
    public function checkPostalBE($i_value)
    {
        if (! \youconix\core\Memory::isTesting()) {
            trigger_error("This function has been deprecated. Please use \youconix\core\services\Validation->checkPostalBE() instead.", E_USER_DEPRECATED);
        }
        return $this->validation->checkPostalBE($i_value);
    }

    /**
     *
     * @param string $s_type
     *            Type of input (GET|POST|REQUEST)
     * @param array $a_declared
     *            De type declare array (init_get,init_post,init_request)
     * @return array The secured input data
     */
    public function secureInput($s_type, $a_declared)
    {
        $a_source = null;
        if ($s_type == 'GET') {
            $a_source = $_GET;
        } else 
            if ($s_type == 'POST') {
                $a_source = $_POST;
            } else 
                if ($s_type == 'REQUEST') {
                    $a_source = $_REQUEST;
                }
        
        if (is_null($a_source))
            return;
        
        if (! array_key_exists('command', $a_declared)) {
            $a_declared['command'] = 'string';
        }
        if (! array_key_exists('AJAX', $a_declared)) {
            $a_declared['AJAX'] = 'string';
        }
        
        $a_init = array_keys($a_declared);
        $a_keys = array_keys($a_source);
        $a_result = array();
        
        foreach ($a_keys as $s_key) {
            if (! in_array($s_key, $a_init)) {
                unset($a_source[$s_key]);
                continue;
            }
            
            switch ($a_declared[$s_key]) {
                case 'boolean':
                    $a_result[$s_key] = $this->secureBoolean($a_source[$s_key]);
                    unset($a_source[$s_key]);
                    break;
                
                case 'int':
                    $a_result[$s_key] = $this->secureInt($a_source[$s_key]);
                    unset($a_source[$s_key]);
                    break;
                
                case 'float':
                    $a_result[$s_key] = $this->secureFloat($a_source[$s_key]);
                    unset($a_source[$s_key]);
                    break;
                
                case 'ignore':
                    $a_result[$s_key] = $a_source[$s_key];
                    unset($a_source[$s_key]);
                    break;
                
                case 'ignore-keep':
                    $a_result[$s_key] = $a_source[$s_key];
                    break;
                
                case 'string':
                    $a_result[$s_key] = $this->secureString($a_source[$s_key]);
                    unset($a_source[$s_key]);
                    break;
                
                case 'string-JS':
                    $a_source[$s_key] = $this->prepareJsDecoding($a_source[$s_key]);
                    $a_result[$s_key] = $this->secureString($a_source[$s_key]);
                    $a_result[$s_key] = $this->fixDecodeBug($a_result[$s_key]);
                    unset($a_source[$s_key]);
                    break;
                
                case 'string-DB':
                    $a_result[$s_key] = $this->secureStringDB($a_source[$s_key]);
                    unset($a_source[$s_key]);
                    break;
                
                case 'string-DB-JS':
                    $a_source[$s_key] = $this->prepareJsDecoding($a_source[$s_key]);
                    $a_result[$s_key] = $this->secureStringDB($a_source[$s_key]);
                    $a_result[$s_key] = $this->fixDecodeBug($a_result[$s_key]);
                    unset($a_source[$s_key]);
                    break;
                
                case 'string-DB-array':
                    $a_result[$s_key] = $this->parseArray('string-DB-array', $a_source[$s_key]);
                    unset($a_source[$s_key]);
                    break;
                
                case 'string-DB-JS-array':
                    $a_result[$s_key] = $this->parseArray('string-DB-JS-array', $a_source[$s_key]);
                    unset($a_source[$s_key]);
                    break;
                
                case 'string-array':
                    $a_result[$s_key] = $this->parseArray('string-array', $a_source[$s_key]);
                    unset($a_source[$s_key]);
                    break;
                
                case 'string-JS-array':
                    $a_result[$s_key] = $this->parseArray('string-JS-array', $a_source[$s_key]);
                    unset($a_source[$s_key]);
                    break;
                
                case 'int-array':
                    $a_result[$s_key] = $this->parseArray('int-array', $a_source[$s_key]);
                    unset($a_source[$s_key]);
                    break;
                
                case 'float-array':
                    $a_result[$s_key] = $this->parseArray('float-array', $a_source[$s_key]);
                    unset($a_source[$s_key]);
                    break;
                
                default:
                    unset($a_source[$s_key]);
                    break;
            }
        }
        return $a_result;
    }

    /**
     * Parses a sub input array
     *
     * @param string $s_type
     *            The data type
     * @param array $a_source
     *            The source array
     * @return array The secured input data
     */
    private function parseArray($s_type, $a_source)
    {
        $a_result = array();
        $a_keys = array_keys($a_source);
        
        foreach ($a_keys as $s_key) {
            if (is_array($a_source[$s_key])) {
                $a_result[$s_key] = $this->parseArray($s_type, $a_source[$s_key]);
            } else 
                if ($s_type == 'string-DB-array') {
                    $a_result[$s_key] = $this->secureStringDB($a_source[$s_key]);
                } else 
                    if ($s_type == 'string-DB-JS-array') {
                        $a_result[$s_key] = $this->prepareJsDecoding($a_source[$s_key]);
                        $a_result[$s_key] = $this->secureStringDB($a_result[$s_key]);
                        $a_result[$s_key] = $this->fixDecodeBug($a_result[$s_key]);
                    } else 
                        if ($s_type == 'string-array') {
                            $a_result[$s_key] = $this->secureString($a_source[$s_key]);
                        } else 
                            if ($s_type == 'string-JS-array') {
                                $a_result[$s_key] = $this->prepareJsDecoding($a_source[$s_key]);
                                $a_result[$s_key] = $this->secureString($a_result[$s_key]);
                                $a_result[$s_key] = $this->fixDecodeBug($a_result[$s_key]);
                            } else 
                                if ($s_type == 'int-array') {
                                    $a_result[$s_key] = $this->secureInt($a_source[$s_key]);
                                } else 
                                    if ($s_type == 'float-array') {
                                        $a_result[$s_key] = $this->secureFloat($a_source[$s_key]);
                                    }
        }
        
        return $a_source;
    }

    /**
     * Prepares the decoding from AJAX requests
     *
     * @param string $s_value
     *            The encoded value
     * @return string The decoded value
     */
    public function prepareJsDecoding($s_value)
    {
        $s_value = str_replace(array(
            '‘',
            '’'
        ), array(
            '&lsquo;',
            '&rsquo;'
        ), urldecode($s_value));
        
        return $s_value;
    }

    /**
     * Fixes the decodeUrl->htmlentities bug
     *
     * @param string $s_text
     *            input text
     * @return string correct decoded text
     */
    public function fixDecodeBug($s_text)
    {
        $s_text = preg_replace('/&acirc;{1}.+&not;{1}/', '&euro;', $s_text);
        
        return str_replace(array(
            '&Atilde;&laquo;',
            '&Atilde;&curren;',
            '&Atilde;&yen;',
            '&Atilde;&sect;',
            '&Atilde;&uml;',
            '&Atilde;&copy;',
            '&Atilde;&macr;',
            '&Atilde;&sup2;',
            '&Atilde;&sup3;',
            '&Atilde;&para;',
            '&Acirc;&copy;',
            '&amp;rsquo;',
            '&amp;lsquo;'
        ), array(
            '&euml;',
            '&auml;',
            '&aring;',
            '&ccedil;',
            '&egrave;',
            '&eacute;',
            '&iuml;',
            '&ograve;',
            '&oacute;',
            '&ouml;',
            '&copy;',
            '&rsquo;',
            '&lsquo;'
        ), $s_text);
    }
}