<?php
namespace youconix\core\models;

/**
 * Parent authorisation class
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 2.0
 */
abstract class LoginParent extends \youconix\core\models\Model
{
    /**
     *
     * @var \LoggerInterface
     */
    protected $logs;

    /**
     *
     * @var \Headers
     */
    protected $headers;

    /**
     *
     * @var \ConfigInterface
     */
    protected $config;

    /**
     *
     * @var \youconix\core\models\User
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
     * @param \LoggerInterface $logs
     * @param \Session $session
     * @param \Headers $headers
     * @param \ConfigInterface $config
     * @param \youconix\core\models\User $user;            
     */
    public function __construct(\Cookie $cookie, \Builder $builder, \LoggerInterface $logs, \Session $session, \Headers $headers, \ConfigInterface $config, \youconix\core\models\User $user)
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
            ->bindString('hash', $s_fingerprint);
        
        $database = $this->builder->getResult();
        if ($database->num_rows() == 0) {
            $i_tries = 1;
            $this->builder->select('login_tries', 'tries')
                ->getWhere()
                ->bindString('ip',$_SERVER['REMOTE_ADDR'])
                ->bindInt('timestamp',array(time(),(time() - 3)),'AND','BETWEEN');
            
            $database = $this->builder->getResult();

            if ($database->num_rows() > 10) {
                $i_tries = 6; // reject login to be sure
            }
            
            $this->builder->insert('login_tries')
                ->bindString('hash',$s_fingerprint)
                ->bindString('ip',$_SERVER['REMOTE_ADDR'])
                ->bindInt('tries',1)
                ->bindInt('timestamp',time())
                ->getResult();
            
            return $i_tries;
        }
        
        $i_tries = ($database->result(0, 'tries') + 1);
        $this->builder->update('login_tries')
            ->bindLiteral('tries', 'tries + 1')
            ->getWhere()
            ->bindString('hash', $s_fingerprint);
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
            ->bindString('hash',$s_fingerprint);
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
                ->bindString('username',$s_username)
                ->bindString('active','1');
            $database = $this->builder->getResult();
            
            if ($database->num_rows() > 0) {
                $s_email = $database->result(0, 'email');
                
                $this->builder->update('users')
                    ->bindString('active', '0')
                    ->getWhere()
                    ->bindString('username', $s_username);
                $this->builder->getResult();
                
                $this->mailer->accountDisableMail($s_username, $s_email);
            }
            
            $this->logs->accountBlockLog($s_username, 3);
        } else 
            if ($this->i_tries == 10) {
                $this->builder->insert('ipban')
                ->bindString('ip', $_SERVER['REMOTE_ADDR'])
                ->getResult();
                $this->ipBlockLog(6);
            } else {
            	$this->loginLog($s_username, 'failed', $this->i_tries);
            }
        
        return false;
    }

    /**
     * Logs the user in
     * @param \youconix\core\models\data\User $user  The user to log in. 
     */
    protected function perform_login(\youconix\core\models\data\User $user)
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
     * @param \youconix\core\models\data\User $user  The user
     */
    public function setLogin(\youconix\core\models\data\User $user)
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
    
        $this->session->setLogin($user->getID(), $user->getUsername(), $i_lastLogin,$user->isBindedToIp());
    
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
        ->bindInt('al.id',$i_id)
        ->bindString('al.IP',$_SERVER['REMOTE_ADDR']);
    
        $database = $this->builder->getResult();
        if ($database->num_rows() == 0) {
            return null;
        }
    
        $a_data = $database->fetch_assoc();
        $user = $this->user->createUser();
        $user->setData($a_data);
    
        if ( $user->isBot() || !$user->isEnabled() || $user->isBlocked() ) {
            $this->builder->delete('autologin')
            ->getWhere()
            ->bindInt('id', $i_id);
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
            ->bindInt('userID', USERID);
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
     * @param \youconix\core\models\data\User $user
     * @param \DomainException  If the current user does not have site admin privileges
     */
    public function loginAs(\youconix\core\models\data\User $user)
    {
        $currentUser = $this->user->get();
        if( !$currentUser->isAdmin(GROUP_ADMIN) ){
            $this->logs->info('User '.$currentUser->username().' tried to take over user session '.$i_userid.'! Access denied!',array('type'=>'securityLog'));
            throw new \DomainException('Only site admins can do this. Access denied!');
        }
            
        $this->session->setLoginTakeover($user->getID(), $user->getUsername(), $user->lastLoggedIn());
        $this->logs->info('login','Site admin '.$currentUser->getUsername().' has logged in as user '.$user->getUsername().' on '.date('Y-m-d H:i:s').'.');
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
     * @param string $s_username
     *            username
     * @param string $s_status
     *            status (failed|success)
     * @param int $i_tries
     *            of login tries
     * @param string $s_openID
     *            default empty
     * @throws \Exception when the log can not be written
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
    
    /**
     * Sets the auto login
     * 
     * @param \youconix\core\models\data\User $user
     */
    protected function setAutoLogin(\youconix\core\models\data\User $user) {
        
            /* Set auto login for the next time */
            $this->builder->delete('autologin')
            ->getWhere()
            ->bindInt('userID', $user->getID());
            $this->builder->getResult();
        
            $this->builder->insert('autologin')
                ->bindInt('userID',$user->getID())
                ->bindString('username',$user->getUsername())
                ->bindString('type',$user->getLoginType())
                ->bindString('IP',$_SERVER['REMOTE_ADDR']);
            
            $database = $this->builder->getResult();
        
            $s_fingerprint = $this->session->getFingerprint();
            $this->cookie->set('autologin', $s_fingerprint . ';' . $database->getID(), '/');
    }
}