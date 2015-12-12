<?php

/** 
 * General Open Authorization parent class                           
 *                                                                              
 * This file is part of Miniature-happiness                                    
 *                                                                              
 * @copyright Youconix                                
 * @author    Rachelle Scheijen                                                
 * @since     1.0 
 *                                                                              
 * Miniature-happiness is free software: you can redistribute it and/or modify 
 * it under the terms of the GNU Lesser General Public License as published by  
 * the Free Software Foundation, either version 3 of the License, or            
 * (at your option) any later version.                                          
 *                                                                              
 * Miniature-happiness is distributed in the hope that it will be useful,      
 * but WITHOUT ANY WARRANTY; without even the implied warranty of               
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                
 * GNU General Public License for more details.                                 
 *                                                                              
 * You should have received a copy of the GNU Lesser General Public License     
 * along with Miniature-happiness.  If not, see <http://www.gnu.org/licenses/>.
 */
abstract class OpenAuth
{

    protected $s_protocol = 'http://';

    protected $s_loginUrl;

    protected $s_logoutUrl;

    protected $s_registrationUrl;

    /**
     * Inits the class OpenAuth
     */
    public function __construct()
    {
        if (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) {
            $this->s_protocol = 'https://';
        }
    }

    /**
     * Performs the login
     */
    abstract public function login();

    /**
     * Completes the login
     *
     * @param String $s_code
     *            response code
     * @return String username, otherwise null
     */
    abstract public function loginConfirm($s_code);

    /**
     * Performs the logout
     */
    abstract public function logout();

    /**
     * Performs the registration
     */
    abstract public function registration();

    /**
     * Completes the registration
     *
     * @param String $s_code
     *            response code
     * @return array login data, otherwise null
     */
    abstract public function registrationConfirm($s_code);
}