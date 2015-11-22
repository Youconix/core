<?php
namespace core\models;

/**
 * Account authorization models Handles login from the accounts This file is part of Miniature-happiness
 * Miniature-happiness is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser
 * General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * Miniature-happiness is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the
 * GNU Lesser General Public License along with Miniature-happiness. If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 2.0
 */
abstract class LoginParent extends Model
{
    /**
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logs;

    /**
     *
     * @var \Headers
     */
    protected $headers;

    /**
     *
     * @var \Config
     */
    protected $config;

    /**
     *
     * @var \core\models\User
     */
    protected $user;

    /**
     *
     * @var \Session
     */
    protected $session;

    /**
     *
     * @var \Cookie
     */
    protected $cookie;
    
    protected $i_tries;

    /**
     * Inits the Login model
     *
     * @param \Cookie $cookie
     * @param \Builder $builder
     * @param \Logger $logs
     * @param \Session $session
     * @param \Headers $headers
     * @param \Config $config
     * @param \core\models\User $user;            
     */
    public function __construct(\Cookie $cookie, \Builder $builder, \Logger $logs, \Session $session, \Headers $headers, \Config $config, \core\models\User $user)
    {    	
        $this->user = $user;
        $this->cookie = $cookie;
        $this->builder = $builder;
        $this->service_Database = $this->builder->getDatabase();
        $this->logs = $logs;
        $this->session = $session;
        $this->headers = $headers;
        $this->config = $config;
    }

    /**
     * Registers the login try
     *
     * @return int number of tries done including this one
     */
    protected function registerLoginTries()
    {
        $s_fingerprint = $this->session->getFingerprint();
        
        $this->builder->select('login_tries', 'tries')
            ->getWhere()
            ->addAnd('hash', 's', $s_fingerprint);
        $service_Database = $this->builder->getResult();
        
        if ($service_Database->num_rows() == 0) {
            $i_tries = 1;
            $this->builder->select('login_tries', 'tries')
                ->getWhere()
                ->addAnd(array(
                'ip',
                'timestamp'
            ), array(
                's',
                'i',
                'i'
            ), array(
                $_SERVER['REMOTE_ADDR'],
                time(),
                (time() - 3)
            ), array(
                '=',
                'BETWEEN'
            ));
            $service_Database = $this->builder->getResult();
            if ($service_Database->num_rows() > 10) {
                $i_tries = 6; // reject login to be sure
            }
            
            $this->builder->insert('login_tries', array(
                'hash',
                'ip',
                'tries',
                'timestamp'
            ), array(
                's',
                's',
                'i',
                'i'
            ), array(
                $s_fingerprint,
                $_SERVER['REMOTE_ADDR'],
                1,
                time()
            ))->getResult();
            
            return $i_tries;
        }
        
        $i_tries = ($service_Database->result(0, 'tries') + 1);
        $this->builder->update('login_tries', 'tries', 'l', 'tries + 1')
            ->getWhere()
            ->addAnd('hash', 's', $s_fingerprint);
        $this->builder->getResult();
        return $i_tries;
    }

    /**
     * Clears the login tries
     */
    protected function clearLoginTries()
    {
        $s_fingerprint = $this->session->getFingerprint();
        
        $this->builder->delete('login_tries')
            ->getWhere()
            ->addAnd('hash', 's', $s_fingerprint);
        $this->builder->getResult();
    }

    /**
     * Checks the number of login tries
     * 
     * @param string $s_username    The username
     * @return boolean  True if the attempt is accepted
     */
    protected function checkTries($s_username)
    {
        $this->i_tries = $this->registerLoginTries();
        
        /* Check the number of tries */
        if ($this->i_tries <= 5) {
            return true;
        }
        if ($this->i_tries == 6) {
            $this->builder->select('users', 'email')
                ->getWhere()
                ->addAnd(array(
                'username',
                'active'
            ), array(
                's',
                's'
            ), array(
                $s_username,
                '1'
            ));
            $service_Database = $this->builder->getResult();
            
            if ($service_Database->num_rows() > 0) {
                $s_email = $service_Database->result(0, 'email');
                
                $this->builder->update('users', 'active', '0')
                    ->getWhere()
                    ->addAnd('username', 's', $s_username);
                $this->builder->getResult();
                
                $this->mailer->accountDisableMail($s_username, $s_email);
            }
            
            $this->logs->accountBlockLog($s_username, 3);
        } else 
            if ($this->i_tries == 10) {
                $this->builder->insert('ipban', 'ip', 's', $_SERVER['REMOTE_ADDR'])->getResult();
                $this->ipBlockLog(6);
            } else {
            	$this->loginLog($s_username, 'failed', $this->i_tries);
            }
        
        return false;
    }

    /**
     * Logs the user in
     * @param \core\models\data\User $user  The user to log in. 
     */
    protected function perform_login(\core\models\data\User $user)
    {
        if ($user->isBot() || ! $user->isEnabled() || $user->isBlocked()) {
            return;
        }
        $this->clearLoginTries();
        $this->loginLog($user->getUsername(), 'success', $this->i_tries);
        
        if ( $user->isPasswordExpired() ) {
            /* Password is expired */
            $this->session->set('expired', $a_data);
            $s_page = str_replace('.php','',$this->config->getPage() );
            $s_page .= '/expired';
            $this->headers->redirect('/'.$s_page);
        }
        $this->setLogin($user);
    }
    
    /**
     * Sets the login session and redirects to the given page or the set default
     *
     * @param \core\models\data\User $user  The user
     */
    public function setLogin(\core\models\data\User $user)
    {
        $s_redirection = $this->config->getLoginRedirect();
    
        if ($this->session->exists('page')) {
            if ($this->session->get('page') != 'logout.php')
                $s_redirection = $this->session->get('page');
    
            $this->session->delete('page');
        }
    
        while (strpos($s_redirection, '//') !== false) {
            $s_redirection = str_replace('//', '/', $s_redirection);
        }
        
        $i_lastLogin = $user->lastLoggedIn();
        $user->updateLastLoggedIn();
    
        $this->session->setLogin($user->getID(), $user->getUsername(), $i_lastLogin);
    
        $this->headers->redirect($s_redirection);
    }
    
    /**
     * Checks if auto login is present and valid.
     * If so, the user is logged in
     */
    public function checkAutologin()
    {
        if (! $this->cookie->exists('autologin')) {
            return;
        }
    
        $s_fingerprint = $this->session->getFingerprint();
        $a_data = explode(';', $this->cookie->get('autologin'));
    
        if ($a_data[0] != $s_fingerprint) {
            $this->cookie->delete('autologin', '/');
            return;
        }
    
        /* Check auto login */
        $user = $this->performAutoLogin($a_data[1]);
        if (is_null($user)) {
            $this->cookie->delete('autologin', '/');
            return;
        }
    
        $this->cookie->set('autologin', implode(';', array($s_fingerprint,$user->getID())), '/');
        $this->setLogin($user);
    }
    
    /**
     * Performs the auto login
     *
     * @param int $i_id
     *            auto login ID
     * @return \data\models\data\User if the login is correct, otherwise null
     */
    protected function performAutoLogin($i_id)
    {
        $this->builder->select('users u', 'u.*');
        $this->builder->innerJoin('autologin al', 'u.id', 'al.userID')
        ->getWhere()
        ->addAnd(array(
            'al.id',
            'al.IP'
        ), array(
            'i',
            's'
        ), array(
            $i_id,
            $_SERVER['REMOTE_ADDR']
        ));
    
        $service_Database = $this->builder->getResult();
        if ($service_Database->num_rows() == 0) {
            return null;
        }
    
        $a_data = $service_Database->fetch_assoc();
        $user = $this->user->createUser();
        $user->setData($a_data);
    
        if ( $user->isBot() || !$user->isEnabled() || $user->isBlocked() ) {
            $this->builder->delete('autologin')
            ->getWhere()
            ->addAnd('id', 'i', $i_id);
            $this->builder->getResult();
            return null;
        }
    
        $this->loginLog($user->getUsername(), 'success', 1);
    
        return $user;
    }
    
    /**
     * Logs the user out
     */
    public function logout()
    {
        if ($this->cookie->exists('autologin')) {
            $this->cookie->delete('autologin', '/');
            $this->builder->delete('autologin')
            ->getWhere()
            ->addAnd('userID', 'i', USERID);
            $this->builder->getResult();
        }
    
        $this->session->destroyLogin();
    
        $s_url = $this->config->getLogoutRedirect();
        $this->headers->redirect($s_url);
    }
    
    /**
     * Logs the user in as the given user
     * Control panel only function
     * This action will be logged
     *
     * @param int $i_userid
     *            The user ID
     * @param \DomainException  If the current user does not have site admin privileges
     */
    public function loginAs($i_userid)
    {
        $currentUser = $this->user->get();
        if( !$currentUser->isAdmin(GROUP_ADMIN) ){
            $this->logs->info('User '.$currentUser->username().' tried to take over user session '.$i_userid.'! Access denied!',array('type'=>'securityLog'));
            throw new \DomainException('Only site admins can do this. Access denied!');
        }
        
        $this->builder->select('users', 'id, nick,lastLogin')
        ->getWhere()
        ->addAnd('id', 'i', $i_userid);
        $service_Database = $this->builder->getResult();
    
        if ($service_Database->num_rows() == 0) {
            return;
        }
    
        $a_data = $service_Database->fetch_assoc();
    
        $this->session->setLoginTakeover($a_data[0]['id'], $a_data[0]['nick'], $a_data[0]['lastLogin']);
        $this->logs->info('login','Site admin '.$currentUser->getUsername().' has logged in as user '.$a_data[0]['nick'].' on '.date('Y-m-d H:i:s').'.');
    }
    
    /**
     * Writes an entry to the account block log
     * 
     * @param string $s_username	The username
     * @param int $i_attemps	Number of login attempts
     */
    protected function accountBlockLog($s_username, $i_attemps)
    {
    	$s_log = 'The account ' . $s_username . ' is disabled on ' . date('d-m-Y H:i:s') . ' after ' . $i_attemps . ' failed login attempts.\n\n System';
    
    	$this->logs->info($s_log, array(
    			'type' => 'accountBlock'
    	));
    }
    
    /**
     * Writes an entry to the account block log
     *
     * @param int $i_attemps	Number of login attempts
     */
    protected function ipBlockLog($i_attemps)
    {
    	$s_log = 'The IP ' . $_SERVER['REMOTE_ADDR'] . ' is blocked on ' . date('d-m-Y H:i:s') . ' after ' . $i_attemps . ' failed login attempts. \n\n System';
    
    	$this->logs->info($s_log, array(
    			'type' => 'accountBlock'
    	));
    }
    
    /**
     * Writes the data to the login log or makes a new one
     *
     * @param String $s_username
     *            username
     * @param String $s_status
     *            status (failed|success)
     * @param int $i_tries
     *            of login tries
     * @param String $s_openID
     *            default empty
     * @throws Exception when the log can not be written
     */
    protected function loginLog($s_username, $s_status, $i_tries, $s_openID = '')
    {
    	if (empty($s_openID)) {
    		$s_log = 'Login to account ' . $s_username . ' from IP : ' . $_SERVER['REMOTE_ADDR'] . ' for ' . $i_tries . ' tries. Status : ' . $s_status . "\n";
    	} else {
    		$s_log = 'Login to account ' . $s_username . ' from IP : ' . $_SERVER['REMOTE_ADDR'] . ' with openID ' . $s_openID . '. Status : ' . $s_status . "\n";
    	}
    
    	$this->logs->info($s_log, array(
    			'type' => 'login'
    	));
    }
    
    protected function setAutoLogin(\core\models\data\User $user) {
        
            /* Set auto login for the next time */
            $this->builder->delete('autologin')
            ->getWhere()
            ->addAnd('userID', 'i', $user->getID());
            $this->builder->getResult();
        
            $this->builder->insert('autologin', array(
                'userID',
                'username',
                'type',
                'IP'
            ), array(
                'i',
                's',
                's',
                's'
            ), array(
                $user->getID(),
                $user->getUsername(),
                $user->getLoginType(),
                $_SERVER['REMOTE_ADDR']
            ));
            $service_Database = $this->builder->getResult();
        
            $s_fingerprint = $this->session->getFingerprint();
            $this->cookie->set('autologin', $s_fingerprint . ';' . $service_Database->getID(), '/');
    }
}