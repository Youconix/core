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
 * Validation class
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @version 1.0
 * @since 2.0
 */
class Validation extends \core\services\Service  implements \Validation
{

    protected $a_errors;

    /**
     * Validates the given email address
     *
     * @param string $s_email
     *            The email address
     * @return boolean True if the email address is valid, otherwise false
     */
    public function checkEmail($s_email)
    {
        if (! filter_var($s_email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        
        return true;
    }

    /**
     * Validates the given URI
     *
     * @param string $s_uri
     *            The URI
     * @return boolean True if the URI is valid, otherwise false
     */
    public function checkURI($s_uri)
    {
        if (preg_match("#^(http://|https://|ftp://|ftps://|file://)+#i", $s_uri)) {
            if (! filter_var($s_uri, FILTER_VALIDATE_URL)) {
                return false;
            }
            
            return true;
        } else {
            if (! preg_match("#^[a-z0-9\-\.]{2,255}\.+[a-z0-9\-]{2,63}#i", $s_uri)) {
                return false;
            }
            return true;
        }
    }

    /**
     * Validates the given dutch postal address
     *
     * @param string $s_value
     *            The postal address
     * @return boolean True if the postal address is valid, otherwise false
     */
    public function checkPostalNL($s_value)
    {
        if (trim($s_value) == "") {
            return false;
        }
        if (! preg_match("/^\d{4}\s?[a-z]{2}$/i", $s_value)) {
            return false;
        }
        
        return true;
    }

    /**
     * Validates the given belgium postal address
     *
     * @param string $s_value
     *            The postal address
     * @return boolean True if the postal address is valid, otherwise false
     */
    public function checkPostalBE($i_value)
    {
        if (trim($i_value) == "") {
            return false;
        }
        if (! preg_match("/^\d{4}$/", $i_value)) {
            return false;
        }
        
        if ($i_value < 1000 || $i_value > 9999) {
            return false;
        }
        
        return true;
    }

    /**
     * Validates the IP address
     *
     * @param string $s_value
     *            The IPv4 or IPv6 address
     * @return boolean True if the address is valid
     */
    public function validateIP($s_value)
    {
        if (substr($s_value, - 3) == '/') {
            $s_value = substr($s_value, 0, - 3);
        }
        $s_value = @inet_pton($s_value);
        
        return ($s_value === false);
    }

    /**
     * Performs the validation
     *
     * @return boolean True if the fields are valid
     */
    public function validate($a_validation, $a_collection)
    {
        $a_keys = array_keys($a_validation);
        $this->a_errors = array();
        
        foreach ($a_keys as $s_key) {
            if (! array_key_exists($s_key, $a_collection)) {
                $this->a_errors[] = 'Error validating non existing field ' . $s_key . '.';
                continue;
            }
            
            $this->validateField($s_key, $a_collection[$s_key], $a_collection[$s_key]);
        }
        
        if (count($this->a_errors) > 0) {
            return false;
        }
        
        return true;
    }

    /**
     * Validates the given field
     *
     * @param string $s_key		The field name
     * @param mixed $field		The  field value
     * @param string $s_rules	The validation rules
     */
    public function validateField($s_key, $field, $s_rules)
    {
        $a_errors = array();
        
        /* Parse rules */
        $a_validation = array();
        $a_rules = explode('|', $s_rules);
        foreach ($a_rules as $s_rule) {
            if ($s_rule == 'required') {
                if ((is_null($field) || trim($field) == '')) {
                    $this->a_errors[] = 'Required field ' . $s_key . ' is not filled in.';
                    return;
                }
            }
            if (strpos($s_rule, 'type:') !== false) {
                $s_type = trim(str_replace('type:', '', $s_rule));
                if (! $this->checkType($s_key,$s_field, $s_type)) {
                    return;
                }
            }
            if (strpos($s_rule, 'pattern:') !== false) {
                $s_pattern = trim(str_replace('pattern:', '', $s_rule));
                $this->checkPattern($s_key,$s_field, $s_pattern);
            }
            if (strpos($s_rule, 'min:') !== false) {
                $fl_minValue = trim(str_replace('min:', '', $s_rule));
                if ($field < $fl_minValue) {
                    $this->a_errors[] = "Field " . $s_key . " is smaller than minimun value " . $fl_minValue . ".";
                }
            }
            if (strpos($s_rule, 'max:') !== false) {
                $fl_maxValue = trim(str_replace('max:', '', $s_rule));
                if ($field > $fl_maxValue) {
                    $this->a_errors[] = "Field " . $s_key . " is bigger than maximun value " . $fl_maxValue . ".";
                }
            }
            if (strpos($s_rule, 'set:') !== false) {
                $s_set = trim(str_replace('set:', '', $s_rule));
                $a_set = explode(',', $s_set);
                
                if (! in_array($field, $a_set)) {
                    $this->a_errors[] = "Field " . $s_key . " has invalid value " . $field . ". Only the values " . $s_set . ' are allowed.';
                }
            }
            if (strpos($s_rule, 'minlength:') !== false) {
                $i_minLength = trim(str_replace('minlength:', '', $s_rule));
                if (strlen($field) < $i_minLength) {
                    $this->a_errors[] = "Field " . $s_key . " is shorter than " . $i_minLength . " characters.";
                }
            }
            if (strpos($s_rule, 'maxlength:') !== false) {
                $i_maxLength = trim(str_replace('minlength:', '', $s_rule));
                if (strlen($field) > $i_maxLength) {
                    $this->a_errors[] = "Field " . $s_key . "is longer than " . $i_maxLength . " characters.";
                }
            }
        }
    }

    protected function checkPattern($s_field, $s_pattern)
    {
        if (is_null($s_field) || trim($s_field) == '') {
            return;
        }
        $bo_pattern = true;
        if (! in_array($s_pattern, array(
            'email',
            'url'
        )) && ! preg_match("/" . $s_pattern . "/", $s_field)) {
            $bo_pattern = false;
        } else 
            if (($s_pattern == 'email' && ! $this->checkEmail($s_field))) {
                $bo_pattern = false;
            } else 
                if ($s_pattern == 'url' && (! $this->checkURI($s_field) && $s_field != 'localhost' && ! $this->validateIP($s_field))) {
                    $bo_pattern = false;
                }
        
        if (! $bo_pattern) {
            $this->a_errors[] = "Field " . $s_key . " does not match pattern " . $s_pattern . ".";
        }
    }

    protected function checkType($s_key,$s_field, $s_expectedType)
    {
        $s_type = gettype($s_field);
        
        if ($s_type == 'integer') {
            $s_type = 'int';
        }
        
        switch ($s_expectedType) {
            case 'port':
                if ($s_type != 'int' && ! is_numeric($s_type)) {
                    $this->a_errors[] = 'Invalid type for field ' . $s_key . '. Found ' . $s_type . ' but expected ' . $s_expectedType . '.';
                }
                if ($s_field < 1 || $s_field > 65535) {
                    $this->a_errors = 'Invalid amount for field ' . $s_key . '. Ports must be between 1 and 65535.';
                }
                break;
            
            case 'int':
                if (($s_type != $s_expectedType) && ! is_numeric($s_field)) {
                    $this->a_errors[] = 'Invalid type for field ' . $s_key . '. Found ' . $s_type . ' but expected ' . $s_expectedType . '.';
                    return false;
                }
                break;
            
            case 'array':
                if ($s_type != $s_expectedType) {
                    $this->a_errors[] = 'Invalid type for field ' . $s_key . '. Found ' . $s_type . ' but expected ' . $s_expectedType . '.';
                    return false;
                }
                break;
            
            case 'float':
                if ($s_type != 'float' && $s_type != 'double' && ! is_numeric($s_field)) {
                    $this->a_errors[] = 'Invalid type for field ' . $s_key . '. Found ' . $s_type . ' but expected ' . $s_expectedType . '.';
                    return false;
                }
                break;
            
            case 'IP':
                if (! $this->validateIP($a_collection[$s_key])) {
                    $a_errors[] = 'Field ' . $s_key . ' is not a valid IP-address';
                    return false;
                }
        }
        
        return true;
    }

    /**
     * Returns the errors after validation
     *
     * @return array The errors
     */
    public function getErrors()
    {
        return $this->a_errors;
    }
}