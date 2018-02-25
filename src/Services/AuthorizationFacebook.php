<?php
namespace youconix\Core\Services;

/**
 * Account authorization service
 * Handles registration and login from the accounts
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @see Core/openID/OpenAuth.inc.php
 * @since 1.0
 */
class Authorization extends AbstractService
{

    protected $service_Cookie;

    protected $service_QueryBuilder;

    protected $service_Logs;

    protected $s_openID_dir;

    protected $a_authorizationTypes = array();

    protected $a_openID_types = array();

    /**
     * Inits the service Autorization
     *
     * @param \Cookie $service_Cookie
     *            The cookie handler
     * @param \Builder $service_QueryBuilder
     *            The query builder
     * @param \LoggerInterface $service_Logs
     *            The log service
     */
    public function __construct(\Cookie $service_Cookie, \Builder $service_QueryBuilder, \LoggerInterface $service_Logs)
    {
        $this->s_openID_dir = NIV . 'include/openID/';
        require_once ($this->s_openID_dir . 'OpenAuth.inc.php');
        
        $this->service_Cookie = $service_Cookie;
        $this->service_QueryBuilder = $service_QueryBuilder;
        $this->service_Database = $this->service_QueryBuilder->getDatabase();
        $this->service_Logs = $service_Logs;
        
        if (! class_exists('\youconix\core\interfaces\Authorization')) {
            require (NIV . 'include/interface/Authorization.inc.php');
        }
        
        $a_types = array(
            'normal',
            'Facebook',
            'OpenID',
            'LDAP'
        );
        foreach ($a_types as $s_type) {
            $authorization = \Loader::inject('\youconix\core\services\Authorization' . ucfirst($s_type), true);
            if( !is_null($authorization) ){
                $this->a_authorizationTypes[$s_type] = $authorization;
                $this->a_openID_types[] = $s_type;
            }
        }
    }

    /**
     * Returns the available openID libs
     *
     * @return array openID lib names
     */
    public function getOpenIDList()
    {
        $a_openID = array();
        foreach ($this->a_openID_types as $a_type) {
            $a_openID[] = $a_type[0];
        }
        
        return $a_openID;
    }

    /**
     * Returns the authorization object
     *
     * @param String $s_type
     *            The authorization type (normal|openID|Facebook|LDAP)
     * @return Authorization The object
     * @throws OutOfBoundsException If $s_type does not exist
     */
    private function getAuthorization($s_type)
    {
        if (! array_key_exists($s_type, $this->a_authorizationTypes)) {
            throw new \OutOfBoundsException('Call to unknown authorization protocol ' . $s_type . '.');
        }
        return $this->a_authorizationTypes[$s_type];
    }

    /**
     * Registers the user
     *
     * @param String $s_type
     *            The authorization type (normal|openID|Facebook|LDAP)
     * @param array $a_data
     *            data
     * @param bool $bo_skipActivation
     *            true to skip sending the activation email (auto activation)
     * @return bool if the user is registrated
     */
    public function register($s_type, $a_data, $bo_skipActivation = false)
    {
        $obj_authorization = $this->getAuthorization($s_type);
        
        return $obj_authorization->register($a_data, $bo_skipActivation);
    }

    /**
     * Activates the user
     *
     *
     * @param String $s_code
     *            The activation code
     * @return boolean True if the user is activated
     */
    public function activateUser($s_type, $s_code)
    {
        $obj_authorization = $this->getAuthorization($s_type);
        
        return $obj_authorization->activateUser($s_code);
    }

    /**
     * Prepares the login
     * Only implemented for openID
     *
     * @param String $s_type
     *            The authorization type (normal|openID|Facebook|LDAP)
     */
    public function loginStart($s_type)
    {
        $obj_authorization = $this->getAuthorization($s_type);
        
        $obj_authorization->loginStart();
    }

    /**
     * Logs the user in
     *
     * @param String $s_type
     *            The authorization type (normal|openID|Facebook|LDAP)
     * @param String $s_username            
     * @param String $s_password
     *            text password
     * @param Boolean $bo_autologin
     *            true for auto login
     * @return array id, username and password_expired if the login is correct, otherwise null
     */
    public function login($s_type, $s_username, $s_password, $bo_autologin = false)
    {
        $obj_authorization = $this->getAuthorization($s_type);
        
        return $obj_authorization->login($s_username, $s_password, $bo_autologin);
    }

    /**
     * Performs the auto login
     *
     * @param int $i_id
     *            auto login ID
     * @return array id, username and password_expired if the login is correct, otherwise null
     */
    public function performAutoLogin($i_id)
    {
        $this->service_QueryBuilder->select('users u', 'u.id, u.nick,u.bot,u.active,u.blocked,u.password_expired,u.lastLogin,u.userType');
        $this->service_QueryBuilder->innerJoin('autologin al', 'u.id', 'al.userID')
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
        
        $service_Database = $this->service_QueryBuilder->getResult();
        if ($service_Database->num_rows() == 0) {
            return null;
        }
        
        $a_data = $service_Database->fetch_assoc();
        
        if ($a_data[0]['bot'] == '1' || $a_data[0]['active'] == '0' || $a_data[0]['blocked'] == '1') {
            $this->service_QueryBuilder->delete('autologin')
                ->getWhere()
                ->addAnd('id', 'i', $i_id);
            $this->service_QueryBuilder->getResult();
            return null;
        }
        
        $this->service_Logs->loginLog($a_data[0]['nick'], 'success', 1);
        
        unset($a_data[0]['bot']);
        unset($a_data[0]['active']);
        unset($a_data[0]['blocked']);
        
        return $a_data[0];
    }

    /**
     * Logs the user out
     *
     * @param String $s_type
     *            The authorization type (normal|openID|Facebook|LDAP)
     */
    public function logout($s_type)
    {
        $obj_authorization = $this->getAuthorization($s_type);
        
        if ($this->service_Cookie->exists('autologin')) {
            $this->service_Cookie->delete('autologin', '/');
            $this->service_QueryBuilder->delete('autologin')
                ->getWhere()
                ->addAnd('userID', 'i', USERID);
            $this->service_QueryBuilder->getResult();
        }
        
        $this->service_Session->destroyLogin();
        
        $obj_authorization->logout(NIV . 'index.php');
    }

    /**
     * Loads the openID class
     *
     * @param String $s_type
     *            name
     * @throws Exception openID libary
     * @return OpenAuth class
     */
    protected function getOpenID($s_type)
    {
        if (array_key_exists($s_type, $this->a_openID)) {
            return $this->a_openID[$s_type];
        }
        
        if (! array_key_exists($s_type, $this->a_openID_types)) {
            throw new \Exception("Unknown openID libary with name " . $s_type);
        }
        
        $a_data = $this->a_openID_types[$s_type];
        require ($this->s_openID_dir . $a_data[1]);
        $obj_ID = new $s_type();
        $this->a_openID[$s_type] = $obj_ID;
        return $obj_ID;
    }

    /**
     * Resends the activation email
     *
     * @param String $s_username
     *            username
     * @param String $s_email
     *            email address
     * @return bool True if the email has been send
     */
    public function resendActivationEmail($s_type, $s_username, $s_email)
    {
        if ($s_type != 'normal') {
            return;
        }
        
        return $obj_authorization = $this->resendActivationEmail($s_username, $s_email);
    }

    /**
     * Registers the password reset request
     *
     * @param String $s_email
     *            email address
     * @return int status code
     *         0	Email address unknown
     *         -1	OpenID account
     *         1 Email send
     */
    public function resetPasswordMail($s_email)
    {
        $this->service_QueryBuilder->select('users', 'id,loginType,nick')
            ->getWhere()
            ->addAnd(array(
            'active',
            'blocked',
            'email'
        ), array(
            's',
            's',
            's'
        ), array(
            1,
            0,
            $s_email
        ));
        $service_Database = $this->service_QueryBuilder->getResult();
        
        if ($service_Database->num_rows() == 0) {
            return 0;
        }
        
        $s_username = $service_Database->result(0, 'nick');
        $i_userid = $service_Database->result(0, 'id');
        $s_loginType = $service_Database->result(0, 'loginType');
        
        if ($s_loginType != 'normal') {
            return - 1;
        }
        
        $service_Random = \youconix\core\Memory::services('Random');
        $s_newPassword = $service_Random->numberLetter(10, true);
        $s_hash = sha1($s_username . $service_Random->numberLetter(20, true) . $s_email);
        
        $s_passwordHash = $this->hashPassword($s_newPassword, $s_username);
        $this->service_QueryBuilder->insert('password_codes', array(
            'userid',
            'code',
            'password',
            'expire'
        ), array(
            'i',
            's',
            's',
            'i'
        ), array(
            $i_userid,
            $s_hash,
            $s_passwordHash,
            (time() + 86400)
        ))->getResult();
        
        Memory::services('Mailer')->passwordResetMail($s_username, $s_email, $s_newPassword, $s_hash);
        
        return 1;
    }

    /**
     * Resets the password
     *
     * @param String $s_hash
     *            reset hash
     * @return boolean if the hash is correct, otherwise false
     */
    public function resetPassword($s_hash)
    {
        $this->service_QueryBuilder->select('password_codes', 'userid,password')
            ->getWhere()
            ->addAnd(array(
            'code',
            'expire'
        ), array(
            's',
            'i'
        ), array(
            $s_hash,
            time()
        ), array(
            '=',
            '>'
        ));
        
        $service_Database = $this->service_QueryBuilder->getResult();
        if ($service_Database->num_rows() == 0) {
            return false;
        }
        
        $i_userid = $service_Database->result(0, 'userid');
        $s_password = $service_Database->result(0, 'password');
        try {
            $this->service_QueryBuilder->transaction();
            
            $this->service_QueryBuilder->delete('password_codes')
                ->getWhere()
                ->addOr(array(
                'code',
                'expire'
            ), array(
                's',
                'i'
            ), array(
                $s_hash,
                time()
            ), array(
                '=',
                '<'
            ));
            $this->service_QueryBuilder->getResult();
            
            $this->service_QueryBuilder->delete('ipban')
                ->getWhere()
                ->addAnd('ip', 's', $_SERVER['REMOTE_ADDR']);
            $this->service_QueryBuilder->getResult();
            $this->clearLoginTries();
            
            $this->service_QueryBuilder->update('users', array(
                'password',
                'active',
                'password_expired'
            ), array(
                's',
                's',
                's'
            ), array(
                $s_password,
                '1',
                '1'
            ));
            $this->service_QueryBuilder->getWhere()->addAnd('id', 'i', $i_userid);
            $this->service_QueryBuilder->getResult();
            
            $this->service_QueryBuilder->commit();
            return true;
        } catch (\DBException $e) {
            $this->service_QueryBuilder->rollback();
            Memory::services('ErrorHandler')->error($e);
            return false;
        }
    }

    /**
     * Disables the account by the username
     * Sends a notification email
     *
     * @param String $s_username
     *            username
     */
    public function disableAccount($s_username)
    {
        \youconix\core\Memory::type('string', $s_username);
        
        try {
            $this->service_QueryBuilder->select('users', 'email')
                ->getWhere()
                ->addAnd('nick', 's', $s_username);
            
            $service_Database = $this->service_QueryBuilder->getResult();
            if ($service_Database->num_rows() == 0)
                return;
            
            $s_email = $service_Database->result(0, 'email');
            
            $this->service_QueryBuilder->transaction();
            
            $this->service_QueryBuilder->update('users', 'active', 's', '0')
                ->getWhere()
                ->addAnd('nick', 's', $s_username);
            $this->service_QueryBuilder->getResult();
            
            /* Send mail to user */
            Memory::services('Mailer')->accountDisableMail($s_username, $s_email);
            
            $this->service_QueryBuilder->commit();
        } catch (Exception $e) {
            $this->service_QueryBuilder->rollback();
            Memory::services('ErrorHandler')->error($e);
        }
    }
}
?>
