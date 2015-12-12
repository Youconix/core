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
 * Service class for handling and manipulating cookies
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @version 1.0
 * @since 1.0
 */
class Cookie extends \core\services\Service implements \Cookie
{

    private $service_Security;

    /**
     * PHP5 constructor
     *
     * @param \Security $service_Security
     *            The security layer
     */
    public function __construct(\Security $service_Security)
    {
        $this->service_Security = $service_Security;
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
     * Encrypts the given string with base64_encode
     *
     * @param String $s_cookieData
     *            The data that needs to be encrypted
     * @return String The encrypted data
     */
    private function encrypt($s_cookieData)
    {
        $s_cookieData = base64_encode($s_cookieData);
        
        return $s_cookieData;
    }

    /**
     * Decrypts the given string with base64_decode
     *
     * @param String $s_cookieData
     *            The data that needs to be decrypted
     * @return String The decrypted data
     */
    private function decrypt($s_cookieData)
    {
        $s_cookieData = base64_decode($s_cookieData);
        
        $s_cookieData = $this->service_Security->secureString($s_cookieData);
        
        return $s_cookieData;
    }

    /**
     * Deletes the cookie with the given name and domain
     *
     * @param String $s_cookieName
     *            The name of the cookie
     * @param String $s_domain
     *            The domain of the cookie
     * @throws Exception if the cookie does not exist
     */
    public function delete($s_cookieName, $s_domain)
    {
        \core\Memory::type('string', $s_cookieName);
        \core\Memory::type('string', $s_domain);
        
        if (! $this->exists($s_cookieName)) {
            throw new \Exception("Cookie " . $s_cookieName . " does not exist.");
        }
        
        @setcookie($s_cookieName, "", time() - 3600, $s_domain);
        if (isset($_COOKIE[$s_cookieName])) {
            unset($_COOKIE[$s_cookieName]);
        }
    }

    /**
     * Sets the cookie with the given name and data
     *
     * @param String $s_cookieName
     *            The name of the cookie
     * @param String $s_cookieData
     *            The data to put into the cookie
     * @param String $s_domain
     *            The domain the cookie schould work on, default /
     * @param String $s_url
     *            The URL the cookie schould work on, optional
     * @param int $i_secure
     *            1 if the cookie schould be https-only otherwise 0, optional
     * @return boolean True if the cookie has been set, false if it has not
     */
    public function set($s_cookieName, $s_cookieData, $s_domain, $s_url = "", $i_secure = 0)
    {
        \core\Memory::type('string', $s_cookieName);
        \core\Memory::type('string', $s_cookieData);
        \core\Memory::type('string', $s_domain);
        \core\Memory::type('string', $s_url);
        \core\Memory::type('int', $i_secure);
        
        $s_cookieData = $this->encrypt($s_cookieData);
        $_COOKIE[$s_cookieName] = $s_cookieData;
        
        if ( @setcookie($s_cookieName, $s_cookieData, time() + 2592000, $s_domain, $s_url, $i_secure)) {
            return true;
        } else {
            if (! defined('DEBUG')) {
                unset($_COOKIE[$s_cookieName]);
            }
            return false;
        }
    }

    /**
     * Receives the content from the cookie with the given name
     *
     * @param String $s_cookieName
     *            The name of the cookie
     * @return String The requested cookie
     * @throws Exception if the cookie does not exist
     */
    public function get($s_cookieName)
    {
        \core\Memory::type('string', $s_cookieName);
        
        if ($this->exists($s_cookieName) == 0) {
            throw new \Exception("Cookie " . $s_cookieName . " does not exist.");
        }
        
        /* Read cookie */
        $s_cookie = $_COOKIE[$s_cookieName];
        $s_cookie = $this->decrypt($s_cookie);
        
        return $s_cookie;
    }

    /**
     * Checks if the given cookie exists
     *
     * @param String $s_cookieName
     *            The name of the cookie you want to check
     * @return boolean True if the cookie exists, false if it does not
     */
    public function exists($s_cookieName)
    {
        \core\Memory::type('string', $s_cookieName);
        
        if (! isset($_COOKIE[$s_cookieName])) {
            return false;
        }
        
        return true;
    }
}