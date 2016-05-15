<?php
namespace youconix\core\services;

/**
 * Session service class for managing sessions and login status
 *
 * This file is part of Miniature-Happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @version 1.0
 * @since 1.0
 */
class Session extends \youconix\core\services\Service implements \Session
{

    /**
     *
     * @var \Builder
     */
    protected $builder;

    /**
     * PHP 5 constructor
     *
     * @param \Settings $settings            
     * @param \Builder $builder            
     */
    public function __construct(\Settings $settings, \Builder $builder)
    {
        $this->builder = $builder;
        $s_sessionSetName = $settings->get('settings/session/sessionName');
        $s_sessionSetPath = $settings->get('settings/session/sessionPath');
        $s_sessionExpire = $settings->get('settings/session/sessionExpire');
        
        if ($s_sessionSetName != '') {
            @session_name($s_sessionSetName);
        }
        if ($s_sessionSetPath != '') {
            @session_save_path($s_sessionSetPath);
        }
        if ($s_sessionExpire != '') {
            @ini_set("session.gc_maxlifetime", $s_sessionExpire);
        }
        
        @session_start();
    }

    /**
     * Returns if the object schould be treated as singleton
     *
     * @return boolean
     */
    public static function isSingleton()
    {
        return true;
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        session_write_close();
    }

    /**
     * Sets the session with the given name and content
     *
     * @param string $s_sessionName
     *            of the session
     * @param string $s_sessionData
     *            of the session
     */
    public function set($s_sessionName, $s_sessionData)
    {
        \youconix\core\Memory::type('string', $s_sessionName);
        
        /* Set session */
        $_SESSION[$s_sessionName] = $s_sessionData;
    }

    /**
     * Deletes the session with the given name
     *
     * @param string $s_sessionName
     *            of the session
     * @throws \IOException if the session does not exist
     */
    public function delete($s_sessionName)
    {
        \youconix\core\Memory::type('string', $s_sessionName);
        
        if (! $this->exists($s_sessionName)) {
            throw new \IOException('Session ' . $s_sessionName . ' does not exist');
        }
        
        unset($_SESSION[$s_sessionName]);
    }

    /**
     * Collects the content of the given session
     *
     * @param string $s_sessionName
     *            name of the session
     * @return string asked session
     * @throws \IOException if the session does not exist
     */
    public function get($s_sessionName)
    {
        \youconix\core\Memory::type('string', $s_sessionName);
        
        if (! $this->exists($s_sessionName)) {
            throw new \IOException('Session ' . $s_sessionName . ' does not exist');
        }
        
        $s_data = $_SESSION[$s_sessionName];
        
        return $s_data;
    }

    /**
     * Checks or the given session exists
     *
     * @param string $s_sessionName
     *            name of the session
     * @return boolean True if the session exists, false if it does not
     */
    public function exists($s_sessionName)
    {
        \youconix\core\Memory::type('string', $s_sessionName);
        
        if (isset($_SESSION[$s_sessionName])) {
            return true;
        }
        
        return false;
    }

    /**
     * Renews the given session
     *
     * @param string $s_sessionName
     *            The name of the session
     */
    public function renew($s_sessionName)
    {
        \youconix\core\Memory::type('string', $s_sessionName);
        
        if ($this->exists($s_sessionName)) {
            $_SESSION[$s_sessionName] = $_SESSION[$s_sessionName];
        }
    }

    /**
     * Destroys all sessions currently set
     */
    public function destroy()
    {
        session_destroy();
        $_SESSION = array();
    }

    /**
     * Logges the user in and sets the login-session
     * Destroys the current session array
     *
     * @param int $i_userid
     *            of the user
     * @param string $s_username
     *            of the user
     * @param int $i_lastLogin
     *            login as a timestamp
     * @param bool  $bo_binded  Set true to bind the session to the ip
     */
    public function setLogin($i_userid, $s_username, $i_lastLogin,$bo_binded)
    {
        /* Get data */
        $this->builder->update('users')
            ->bindInt('lastLogin', time())
            ->getWhere()
            ->bindInt('id', $i_userid);
        $this->builder->getResult();
        
        session_regenerate_id(true);
        $_SESSION = array();
        
        $this->set('login', '1');
        $this->set('userid', $i_userid);
        $this->set('username', $s_username);
        $this->set('fingerprint', $this->getFingerprint());
        $this->set('lastLogin', $i_lastLogin);
        if( $bo_binded ){
            $this->set('bind_ip','1');
        }
    }

    /**
     * Logs the admin in with the given userid and username
     * Admin session wil be restored at logout
     * Destroys the current session array
     *
     * @param int $i_userid
     *            of the user
     * @param string $s_username
     *            of the user
     * @param int $i_lastLogin
     *            login as a timestamp
     */
    public function setLoginTakeover($i_userid, $s_username, $i_lastLogin)
    {
        $a_lastLogin = array(
            'userid' => $this->get('userid'),
            'username' => $this->get('username'),
            'lastLogin' => $this->get('lastLogin')
        );
        
        $this->setLogin($i_userid, $s_username, $i_lastLogin);
        $this->set('last_login', $a_lastLogin);
    }

    /**
     * Destroys the users login session
     */
    public function destroyLogin()
    {
        if ($this->exists('login'))
            $this->delete('login');
        if ($this->exists('userid'))
            $this->delete('userid');
        if ($this->exists('username'))
            $this->delete('username');
        if ($this->exists('fingerprint'))
            $this->delete('fingerprint');
        if ($this->exists('lastLogin'))
            $this->delete('lastLogin');
        if ($this->exists('type'))
            $this->delete('type');
        
        if ($this->exists('last_login')) {
            $a_login = $this->get('last_login');
            $this->setLogin($a_login['userid'], $a_login['username'], $a_login['lastLogin']);
        }
    }

    /**
     * Returns the visitors browser fingerprint
     *
     * @return String fingerprint
     */
    public function getFingerprint()
    {
        $s_encoding = str_replace(', sdch', '', $_SERVER['HTTP_ACCEPT_ENCODING']);
        if ($this->exists('bind_ip')) {
            return sha1($_SERVER['REMOTE_ADDR'] . '-' . $_SERVER['HTTP_USER_AGENT'] . '-' . $_SERVER['HTTP_HOST'] . '-' . $_SERVER['SERVER_SIGNATURE'] . '-' . strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']) . '-' . $s_encoding);
        } else {
            return sha1($_SERVER['HTTP_USER_AGENT'] . '-' . $_SERVER['HTTP_HOST'] . '-' . $_SERVER['SERVER_SIGNATURE'] . '-' . strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']) . '-' . $s_encoding);
        }
    }
}