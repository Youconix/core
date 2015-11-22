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
class Login extends LoginParent
{

    /**
     *
     * @var \Hashing
     */
    protected $hashing;

    /**
     *
     * @var \Mailer
     */
    protected $mailer;

    /**
     *
     * @var \core\services\Random
     */
    protected $random;

    /**
     * Inits the autorization model
     *
     * @param \Cookie $cookie            
     * @param \Builder $builder            
     * @param \Logger $logs            
     * @param \core\services\Hashing $hashing            
     * @param \Session $session            
     * @param \core\services\Mailer $mailer            
     * @param \core\services\Random $random            
     * @param \Headers $headers            
     * @param \Config $config            
     * @param \core\models\User $user;            
     */
    public function __construct(\Cookie $cookie, \Builder $builder, \Logger $logs, \core\services\Hashing $hashing, \Session $session, \core\services\Mailer $mailer, \core\services\Random $random, \Headers $headers, \Config $config, \core\models\User $user)
    {
        parent::__construct($cookie, $builder, $logs, $session, $headers, $config, $user);
        
        $this->hashing = $hashing;
        $this->mailer = $mailer;
        $this->random = $random;
    }

    /**
     * Logs the user in
     *
     * @param string $s_username
     *            The username
     * @param string $s_password
     *            text password
     * @param boolean $bo_autologin
     *            true for auto login
     */
    public function do_login($s_username, $s_password, $bo_autologin = false)
    {
        if (! $this->checkTries($s_username)) {
            return;
        }
        
        $s_salt = $this->user->getSalt($s_username, 'normal');
        if (is_null($s_salt)) {
            return;
        }
        
        $s_passwordHash = $this->hashing->hashUserPassword($s_password, $s_salt);

        /* Check the login combination */
        $this->builder->select('users', '*');
        $this->builder->getWhere()->addAnd(array(
            'nick',
            'password',
            'active',
            'loginType'
        ), array(
            's',
            's',
            's',
            's'
        ), array(
            $s_username,
            $s_passwordHash,
            '1',
            'normal'
        ));
        $service_Database = $this->builder->getResult();
        
        if ($service_Database->num_rows() == 0) {
            /* Check old way */
            $s_password = $this->hashPassword($s_password, $s_username);
            $this->builder->select('users', '*');
            $this->builder->getWhere()->addAnd(array(
                'nick',
                'password',
                'active',
                'loginType'
            ), array(
                's',
                's',
                's',
                's'
            ), array(
                $s_username,
                $s_password,
                '1',
                'normal'
            ));
            $service_Database = $this->builder->getResult();
            if ($service_Database->num_rows() == 0) {
                return;
            }
            
            /* Update user record */
            $i_id = $service_Database->result(0, 'id');
            $builder = clone $this->builder;
            $builder->update('users', 'password', 's', $s_passwordHash)
                ->getWhere()
                ->addAnd('id', 'i', $i_id);
            $builder->getResult();
        }
        
        $a_data = $service_Database->fetch_assoc();
        $user = $this->user->createUser();
        $user->setData($a_data[0]);
        if( $bo_autologin ){
            $this->setAutoLogin($user);
        }
        return parent::perform_login($user);
    }

    /**
     * Registers the password reset request
     *
     * @param string $s_email
     *            email address
     * @return int status code 0 Email address unknown -1 OpenID account 1 Email send
     */
    public function resetPasswordMail($s_email)
    {
        $this->builder->select('users', 'id,loginType,nick')
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
        $service_Database = $this->builder->getResult();
        
        if ($service_Database->num_rows() == 0) {
            return 0;
        }
        
        $s_username = $service_Database->result(0, 'nick');
        $i_userid = $service_Database->result(0, 'id');
        $s_loginType = $service_Database->result(0, 'loginType');
        
        if ($s_loginType != 'normal') {
            return - 1;
        }
        
        $s_newPassword = $this->random->numberLetter(10, true);
        $s_hash = sha1($s_username . $this->random->numberLetter(20, true) . $s_email);
        
        $s_passwordHash = $this->hashPassword($s_newPassword, $s_username);
        $this->builder->insert('password_codes', array(
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
        
        $this->mailer->passwordResetMail($s_username, $s_email, $s_newPassword, $s_hash);
        
        return 1;
    }

    /**
     * Resets the password
     *
     * @param string $s_hash
     *            reset hash
     * @return boolean if the hash is correct, otherwise false
     */
    public function resetPassword($s_hash)
    {
        $this->builder->select('password_codes', 'userid,password')
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
        
        $service_Database = $this->builder->getResult();
        if ($service_Database->num_rows() == 0) {
            return false;
        }
        
        $i_userid = $service_Database->result(0, 'userid');
        $s_password = $service_Database->result(0, 'password');
        try {
            $this->builder->transaction();
            
            $this->builder->delete('password_codes')
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
            $this->builder->getResult();
            
            $this->builder->delete('ipban')
                ->getWhere()
                ->addAnd('ip', 's', $_SERVER['REMOTE_ADDR']);
            $this->builder->getResult();
            $this->clearLoginTries();
            
            $this->builder->update('users', array(
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
            $this->builder->getWhere()->addAnd('id', 'i', $i_userid);
            $this->builder->getResult();
            
            $this->builder->commit();
            return true;
        } catch (\DBException $e) {
            $this->builder->rollback();
            throw $e;
        }
    }

    /**
     * Disables the account by the username Sends a notification email
     *
     * @param string $s_username
     *            username
     */
    public function disableAccount($s_username)
    {
        \core\Memory::type('string', $s_username);
        
        try {
            $this->builder->select('users', 'email')
                ->getWhere()
                ->addAnd('nick', 's', $s_username);
            
            $service_Database = $this->builder->getResult();
            if ($service_Database->num_rows() == 0)
                return;
            
            $s_email = $service_Database->result(0, 'email');
            
            $this->builder->transaction();
            
            $this->builder->update('users', 'active', 's', '0')
                ->getWhere()
                ->addAnd('nick', 's', $s_username);
            $this->builder->getResult();
            
            /* Send mail to user */
            $this->mailer->accountDisableMail($s_username, $s_email);
            
            $this->builder->commit();
        } catch (\Exception $e) {
            $this->builder->rollback();
            throw $e;
        }
    }

    /**
     * Hashes the given password with the set salt and sha1
     * Old hashing method
     *
     * @param string $s_password
     *            The password
     * @param string $s_username
     *            The username
     * @return string The hashed password
     */
    protected function hashPassword($s_password, $s_username)
    {
        $settings = $this->config->getSettings();
        
        $s_salt = $settings->get('settings/main/salt');
        
        return sha1(substr(md5($s_username), 5, 30) . $s_password . $s_salt);
    }

    /**
     * Registers the user
     *
     * @param array $a_data
     *            data
     * @param bool $bo_skipActivation
     *            true to skip sending the activation email (auto activation)
     * @return bool if the user is registrated
     * @throws \Exception If registrating failes
     */
    public function register($a_data, $bo_skipActivation = false)
    {
        $s_username = $a_data['username'];
        $s_password = $a_data['password'];
        $s_email = $a_data['email'];
        
        try {
            $this->builder->transaction();
            
            $s_registrationKey = sha1(time() . ' ' . $s_username . ' ' . $s_email);
            
            $obj_User = $this->user->createUser();
            $obj_User->setUsername($s_username);
            $obj_User->setEmail($s_email);
            $obj_User->setLoginType('normal');
            $obj_User->setPassword($s_password,'normal');
            $obj_User->setActivation($s_registrationKey);
            $obj_User->setBot(false);
            $obj_User->save();
            
            if (! $bo_skipActivation) {
                $this->sendActivationEmail($s_username, $s_email, $s_registrationKey);
            }
            
            $this->builder->commit();
            
            if (! $bo_skipActivation) {
                $obj_User->activate($s_registrationKey);
            }
            
            return true;
        } catch (\Exception $e) {
            $this->builder->rollback();
            
            throw $e;
        }
    }

    /**
     * Resends the activation email
     *
     * @param string $s_username
     *            username
     * @param string $s_email
     *            email address
     * @return boolean True if the email has been send
     */
    public function resendActivationEmail($s_username, $s_email)
    {
        $user = $this->user->getByName($s_username, $s_email);
        if (is_null($user)) {
            return false;
        }
        
        try {
            $this->sendActivationEmail($user);
            
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    public function changePassword($s_passwordOld,$s_passwordNew){
    	if (! $this->session->exists('expired')) {
    		$this->headers->redirect('index/view');
    	}
    	
    	$a_data = $this->session->get('expired');
    	$user = $this->user->createUser();
    	$user->setData($a_data);
    	
    	if (! $user->changePassword($s_passwordOld,$s_passwordNew) ){
    		return false;
    	}
    	
    	$this->session->delete('expired');
    	
    	$this->setLogin($user);
    	
    	return true;
    }

    /**
     * Sends the activation email
     *
     * @param \core\models\data\User $user            
     * @throws \RuntimeException If the sending of the email failes
     */
    private function sendActivationEmail(\core\models\data\User $user)
    {
        if (! $this->mailer->registrationMail($user->getUsername(), $user->getEmail(), $user->getActivation())) {
            throw new \RuntimeException("Sending registration mail to '.$user->getEmail().' failed.");
        }
    }
}