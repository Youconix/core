<?php
namespace youconix\core\services;

/**
 * Hashing service
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @version 1.0
 * @since 2.0
 */
class Hashing extends Service
{
  /**
   *
   * @var \youconix\core\services\Random
   */
    protected $random;
    
    public function __construct(\youconix\core\services\Random $random){
      $this->random = $random;
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
     * Creates a hash
     *
     * @param string $s_text
     *            The text
     * @return string The hash
     */
    public function hash($s_text)
    {        
        return password_hash($s_text,PASSWORD_BCRYPT );
    }

    /**
     * Verifies the text against the hash
     *
     * @param string $s_text
     *            The text
     * @param string $s_stored
     *            The hashed text
     * @return boolean True if the text is the same
     */
    public function verify($s_text, $s_stored)
    {
        return password_verify($s_text,$s_stored);
    }

    /**
     * Creates a salt
     *
     * @return string The salt
     */
    public function createSalt()
    {
        if (function_exists('openssl_random_pseudo_bytes')) {
            return bin2hex(openssl_random_pseudo_bytes(30));
        }
        
        return $this->random->randomAll(30);
    }
    
    /**
     * Creates a random string with the given length
     * 
     * @param int $i_length
     * @return string
     */
    public function createRandom($i_length = 15){
      return $this->random->randomAll($i_length);
    }
}