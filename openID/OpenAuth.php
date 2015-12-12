<?php
namespace youconix\core\openID;

/**
 * General Open Authorization parent class
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
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