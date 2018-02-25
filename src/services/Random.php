<?php
namespace youconix\core\services;

/**
 * Random generator service
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @version 1.0
 * @since 1.0
 */
class Random extends Service
{

    /**
     * Generates a random code of letters
     *
     * @param int $i_length
     *            The length of the code
     * @param boolean $bo_uppercase
     *            Set to true to use also uppercase letters
     * @return string A random letter-string
     */
    public function letter($i_length, $bo_uppercase = false)
    {
        $s_codeString = 'abcdefghijklmnopqrstuvwxyz';
        if ($bo_uppercase) {
            $s_codeString = 'aAbBcCdDeEfFgGhHiIjJkKlLmMnNoOpPqQrRsStTuUvVwWxXyYzZ';
        }
        
        $i_letters = strlen($s_codeString) - 1;
        $s_code = '';
        for ($i = 1; $i <= $i_length; $i ++) {
            $s_num = rand(0, $i_letters);
            
            $s_code .= $s_codeString[$s_num];
        }
        
        return $s_code;
    }

    /**
     * Generates a random code of numbers
     *
     * @param int $i_length
     *            The length of the code
     * @return string A random number-string
     */
    public function number($i_length)
    {
        $s_codeString = '1234567890';
        $s_code = '';
        
        for ($i = 1; $i <= $i_length; $i ++) {
            $s_num = rand(0, 10);
            
            $s_code .= $s_codeString[$s_num];
        }
        
        return $s_code;
    }

    /**
     * Generates a random code of numbers and letters
     *
     * @param int $i_length
     *            The length of the code
     * @param boolean $bo_uppercase
     *            Set to true to use also uppercase letters
     * @return string A random letter and number-string
     */
    public function numberLetter($i_length, $bo_uppercase)
    {
        $s_codeString = 'abcdefghijklmnopqrstuvwxyz1234567890';
        if ($bo_uppercase) {
            $s_codeString = 'aAbBcCdDeEfFgGhHiIjJkKlLmMnNoOpPqQrRsStTuUvVwWxXyYzZ1234567890';
        }
        
        $i_letters = strlen($s_codeString) - 1;
        $s_code = '';
        for ($i = 1; $i <= $i_length; $i ++) {
            $s_num = rand(0, $i_letters);
            
            $s_code .= $s_codeString[$s_num];
        }
        
        return $s_code;
    }

    /**
     * Generates a random code of numbers and letters for a captcha
     *
     * @param int $i_length
     *            The length of the code
     * @return string A random letter and number-string
     */
    public function numberLetterCaptcha($i_length)
    {
        $s_codeString = 'abcdefhjkmnpqrstuvwxyz23456789';
        
        $i_letters = strlen($s_codeString) - 1;
        $s_code = '';
        for ($i = 1; $i <= $i_length; $i ++) {
            $s_num = rand(0, $i_letters);
            
            $s_code .= $s_codeString[$s_num];
        }
        
        return $s_code;
    }

    /**
     * Generates a random code of all signs
     *
     * @param int $i_length
     *            The length of the code
     * @return string A random sign-string
     */
    public function randomAll($i_length)
    {
        $s_codeString = 'aAbBcCdDeEfFgGhHiIjJkKlLmMnNoOpPqQrRsStTuUvVwWxXyYzZ1234567890`~!@#$%^&*()-_+={[}];:\|<,>.?/';
        
        $i_letters = (strlen($s_codeString)-1);
        $s_code = '';
        for ($i = 1; $i <= $i_length; $i ++) {
            $s_num = rand(0, $i_letters);
            
            $s_code .= $s_codeString[$s_num];
        }
        
        return $s_code;
    }
}