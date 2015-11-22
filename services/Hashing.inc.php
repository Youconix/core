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
     * @var \core\services\Random
     */
    private $random;

    private $obj_hashing = null;

    private $s_systemSalt;

    /**
     * PHP 5 constructor
     *
     * @param \Logger $logs
     *            The log service
     * @param \core\services\Settings $settings
     *            The settings service
     * @param \core\services\Random $random
     *            The random generator
     */
    public function __construct(\Logger $logs, \Settings $settings, \core\services\Random $random)
    {
        $this->random = $random;
        if (! function_exists('password_hash')) {
            if (CRYPT_BLOWFISH != 1) {
                /*
                 * Fallback
                 * Security warning
                 */
                $this->obj_hashing = new HashingFallback();
                $logs->warning('Missing bcrypt and CRYPT_BLOWFISH. Falling back to sha1 hashing. Upgrade your PHP-installation to min. 5.5 at ones!');
            } else {
                /*
                 * Legancy
                 * Security warning
                 */
                $this->obj_hashing = new HashLegancy();
                $logs->warning('Missing bcrypt. Falling back to crypt() with CRYPT_BLOWFISH hashing. Upgrade your PHP-installation to min. 5.5 as soon as possible!');
            }
        } else {
            $this->obj_hashing = new HashNormal();
        }
        
        $this->s_systemSalt = $settings->get('settings/main/salt');
        
        $i_length = strlen($this->s_systemSalt);
        if ($i_length < 22) {
            $this->s_systemSalt .= substr($this->s_systemSalt, 0, (22 - $i_length));
        }
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
     * @param string $s_text    The text
     * @param string $s_salt   The salt
     * @return string   The hash
     */
    public function hash($s_text, $s_salt)
    {
        return $this->obj_hashing->hash($s_text, $s_salt);
    }

    /**
     * Verifies the text against the hash
     * 
     * @param string $s_text    The text
     * @param string $s_stored  The hashed text
     * @param string $s_salt   The salt
     * @return boolean  True if the text is the same
     */
    public function verify($s_text, $s_stored, $s_salt)
    {
        return $this->obj_hashing->verify($s_text, $s_stored, $s_salt);
    }

    /**
     * Hashes the user password login
     * 
     * @param string $s_password    The password
     * @param string $s_username    The username
     * @return string   The hash    
     */
    public function hashUserPassword($s_password, $s_username)
    {
        return $this->obj_hashing->hashUserPassword($s_username, $s_password, $this->s_systemSalt);
    }

    /**
     * Verifies the user login
     * 
     * @param string $s_password    The password
     * @param string $s_username    The username
     * @param string $s_stored  The stored hash
     * @return  bool    True if the login is correct
     */
    public function verifyUserPassword($s_username, $s_password, $s_stored)
    {
        return $this->obj_hashing->verifyUserPassword($s_username, $s_password, $s_stored, $this->s_systemSalt);
    }

    /**
     * Creates a salt
     * 
     * @return string   The salt
     */
    public static function createSalt()
    {
        if (function_exists('openssl_random_pseudo_bytes')) {
            return bin2hex(openssl_random_pseudo_bytes(30));
        }
        
        return $this->random->randomAll(30);
    }
}

abstract class HashingParent
{

    /**
     * Creates a hash
     *
     * @param string $s_text    The text
     * @param string $s_salt   The salt
     * @return string   The hash
     */
    abstract public function hash($s_text, $s_salt);

    /**
     * Verifies the text against the hash
     *
     * @param string $s_text    The text
     * @param string $s_stored  The hashed text
     * @param string $s_salt   The salt
     * @return boolean  True if the text is the same
     */
    public function verify($s_text, $s_stored, $s_salt)
    {
        $s_input = $this->hash($s_text, $s_salt);
        
        return $s_input === $s_stored;
    }

    /**
     * Hashes the user password login
     *
     * @param string $s_password    The password
     * @param string $s_username    The username
     * @return string   The hash
     */
    public function hashUserPassword($s_username, $s_password, $s_salt)
    {
        $s_text = $this->createUserPassword($s_username, $s_password);
        $s_hash = $this->hash($s_text, $s_salt);
        
        $i_missing = (60 - strlen($s_hash));
        if ($i_missing > 0) {
            $s_hash = $s_hash .= substr($s_hash, 0, $i_missing);
        }
        
        return $s_hash;
    }

    /**
     * Verifies the user login
     *
     * @param string $s_password    The password
     * @param string $s_username    The username
     * @param string $s_stored  The stored hash
     * @return  bool    True if the login is correct
     */
    public function verifyUserPassword($s_username, $s_password, $s_stored, $s_salt)
    {
        $s_text = $this->hashUserPassword($s_username, $s_password, $s_salt);
        return ($s_text === $s_text);
    }

    /**
     * Creates the hashed user password
     * 
     * @param string $s_password    The password
     * @param string $s_username    The username
     * @return string   The hash
     */
    protected function createUserPassword($s_username, $s_password)
    {
        return substr(md5(strtolower($s_username)), 5, 30) . $s_password;
    }
}

class HashNormal extends HashingParent
{

    /**
     * Creates a hash
     *
     * @param string $s_text    The text
     * @param string $s_salt   The salt
     * @return string   The hash
     */
    public function hash($s_text, $s_salt)
    {
        $a_options = array(
            'salt' => $s_salt
        );
        
        $s_hash = password_hash($s_text, PASSWORD_BCRYPT, $a_options);
        return $s_hash;
    }

    /**
     * Hashes the user password
     *
     * @param string $s_password    The password
     * @param string $s_username    The username
     * @return string   The hash
     */
    public function hashUserPassword($s_username, $s_password, $s_salt)
    {
        $a_options = array(
            'salt' => $s_salt
        );
        $s_text = $this->createUserPassword($s_username, $s_password);
        
        $s_hash = password_hash($s_text, PASSWORD_BCRYPT, $a_options);
        
        return $s_hash;
    }
    
    /**
     * Creates the hashed user password
     *
     * @param string $s_password    The password
     * @param string $s_username    The user specific salt instead of the username
     * @return string   The hash
     */
    protected function createUserPassword($s_username, $s_password)
    {
        return $s_username . $s_password;
    }
}

class HashLegancy extends HashingParent
{

    /**
     * Creates a hash
     *
     * @param string $s_text    The text
     * @param string $s_salt   The salt
     * @return string   The hash
     */
    public function hash($s_text, $s_salt)
    {
        return crypt($s_text, $s_salt);
    }
}

class HashingFallback extends HashingParent
{

    /**
     * Creates a hash
     *
     * @param string $s_text    The text
     * @param string $s_salt   The salt
     * @return string   The hash
     */
    public function hash($s_text, $s_salt)
    {
        return sha1($s_text, $s_salt);
    }
}