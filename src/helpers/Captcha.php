<?php
namespace youconix\core\helpers;

/**
 * Helper for generating capchas
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
 */
class Captcha extends \youconix\core\helpers\Helper
{

    /**
     *
     * @var \Session
     */
    protected $session;

    /**
     *
     * @var \Youconix\core\services\Random
     */
    protected $random;

    protected $i_length = 8;

    /**
     * PHP 5 constructor
     *
     * @param \Session $session            
     * @param \youconix\core\services\Random $random            
     */
    public function __construct(\Session $session, \youconix\core\services\Random $random)
    {
        $this->session = $session;
        $this->random = $random;
    }

    /**
     * Generates the capcha image
     */
    public function generateCapcha()
    {
        $s_code = $this->generateCode();
        
        $this->session->set('capcha', $s_code);
        /* Image from 150 at 50 px */
        $s_image = imagecreatetruecolor(280, 40);
        
        /* Background color white */
        $background = imagecolorallocate($s_image, 255, 255, 255);
        
        $a_fonts = array(
            NIV . "fonts/route_3.ttf"
        );
        
        /* Text size 5 */
        $size = 5;
        
        /* Text color red */
        $color = imagecolorallocate($s_image, 255, 0, 0);
        
        /* Build image */
        $s_white = imagecolorallocate($s_image, 255, 255, 255);
        $s_black = imagecolorallocate($s_image, 0, 0, 0);
        $i_left = - 25;
        for ($i = 0; $i < $this->i_length; $i ++) {
            $i_text1 = mt_rand(1, 255); // RGB
            $i_text2 = mt_rand(0, 255); // RGB
            $i_text3 = mt_rand(0, 255); // RGB
            $s_text = imagecolorallocate($s_image, $i_text1, $i_text2, $i_text3);
            
            $i_size = mt_rand(18, 22); // Font-size?
            $i_angle = mt_rand(0, 45); // angle
            if (mt_rand(0, 1) == 1) {
                $i_angle *= - 1;
            }
            
            $i_up1 = mt_rand(25, 35); // How much pixels from up?
            $i_up2 = $i_up1 - 1; // Shade
            $i_up3 = $i_up1 + 2; // Shade
            
            $i_left = $i_left + 34; // Letters zijn nu eenmaal breed...
            $i_left1 = $i_left; // Hoeveel pixels van links?
            $i_left2 = $i_left1 - 3; // Schaduw
            $i_left3 = $i_left1 + 3; // Schaduw
            
            $i_random_font = array_rand($a_fonts);
            
            imagettftext($s_image, $i_size, $i_angle, $i_left3, $i_up2, $s_white, $a_fonts[$i_random_font], $s_code[$i]);
            imagettftext($s_image, $i_size, $i_angle, $i_left2, $i_up3, $s_black, $a_fonts[$i_random_font], $s_code[$i]);
            imagettftext($s_image, $i_size, $i_angle, $i_left1, $i_up1, $s_text, $a_fonts[$i_random_font], $s_code[$i]);
        }
        
        ob_clean();
        /*
         * Write image
         */
        header("Expires: Mon, 26 Jul 1990 05:00:00 GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header('Content-type: image/png');
        ImagePng($s_image);
        
        ImageDestroy($s_image);
    }

    /**
     * Generates the capcha code
     *
     * @return string The code
     */
    private function generateCode()
    {
        $s_code = $this->random->numberLetterCaptcha($this->i_length);
        
        return $s_code;
    }

    /**
     * Checks if the given code is correct
     *
     * @param string $s_code
     *            The filled in code, case insensitive
     * @return boolean True if the code is correct, otherwise false
     */
    public function checkCaptcha($s_code)
    {
        $s_code = strtolower($s_code);
        
        if (! $this->session->exists('capcha')) {
            return false;
        }
        
        if ($this->session->get('capcha') != $s_code) {
            $this->session->delete('capcha');
            return false;
        }
        
        $this->session->delete('capcha');
        return true;
    }
}