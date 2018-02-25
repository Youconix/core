<?php
namespace youconix\Core\Services\Data;

class AuthorizationNormal extends \youconix\core\Services\AbstractService implements \youconix\Core\Interfaces\AuthorizationInterface
{

    private $builder;

    private $service_Database;

    private $model_User;

    private $model_PM;

    private $service_Logs;

    /**
     * PHP 5 constructor
     *
     * @param \Builder $builder	The query builder
     * @param \LoggerInterface $service_Logs
     *            The log service
     * @param \youconix\core\models\User $model_User
     *            The user model
     * @param \youconix\core\models\PM $model_PM
     *            The personal message service
     */
    public function __construct(\Builder $builder, \LoggerInterface $service_Logs, \youconix\core\models\User $model_User, \youconix\core\models\PM $model_PM)
    {
        $this->builder = $builder;
        $this->service_Database = $this->builder->getDatabase();
        $this->service_Logs = $service_Logs;
        $this->model_User = $model_User;
        $this->model_PM = $model_PM;
    }

    /**
     * Registers the user
     *
     * @param array $a_data
     *            data
     * @param bool $bo_skipActivation
     *            true to skip sending the activation email (auto activation)
     * @return bool if the user is registrated
     */
    public function register($a_data, $bo_skipActivation = false)
    {
        $s_username = $a_data['username'];
        $s_forname = $a_data['forname'];
        $s_nameBetween = $a_data['nameBetween'];
        $s_surname = $a_data['surname'];
        $s_password = $a_data['password'];
        $s_email = $a_data['email'];
        
        try {
            $this->service_Database->transaction();
            
            $s_registrationKey = sha1(time() . ' ' . $s_username . ' ' . $s_email);
            
            $obj_User = $this->model_User->createUser();
            $obj_User->setUsername($s_username);
            $obj_User->setName($s_forname);
            $obj_User->setNameBetween($s_nameBetween);
            $obj_User->setSurname($s_surname);
            $obj_User->setEmail($s_email);
            $obj_User->setPassword($s_password);
            $obj_User->setActivation($s_registrationKey);
            $obj_User->setBot(false);
            $obj_User->save();
            
            if (! $bo_skipActivation) {
                $this->sendActivationEmail($s_username, $s_email, $s_registrationKey);
            }
            
            $this->service_Database->commit();
            
            if (! $bo_skipActivation) {
                $this->model_User->activateUser($s_registrationKey);
            }
            
            return true;
        } catch (\Exception $e) {
            $this->service_Database->rollback();
            
            \code\Memory::services('ErrorHandler')->error($e);
            return false;
        }
    }

    /**
     * Activates the user
     *
     * @param String $s_code
     *            The activation code
     * @return boolean True if the user is activated
     */
    public function activateUser($s_code)
    {
        $this->builder->select('users', 'id')
            ->getWhere()
            ->addAnd('activation', 's', $s_code);
        $service_Database = $this->builder->getResult();
        if ($service_Database->num_rows() == 0) {
            return false;
        }
        
        $i_userid = $service_Database->result(0, 'id');
        
        try {
            $this->builder->transaction();
            
            $this->builder->insert('profile', 'userid', 'i', $i_userid)->getResult();
            
            $this->builder->update('users', array(
                'activation',
                'active'
            ), array(
                's',
                's'
            ), array(
                '',
                '1'
            ));
            $this->builder->getWhere()->addAnd('id', 'i', $i_userid);
            $this->builder->getResult();
            
            define('USERID', $i_userid);
            
            $this->builder->commit();
            
            return true;
        } catch (\Exception $e) {
            $this->builder->rollback();
            \youconix\core\Memory::services('ErrorHandler')->error($e);
            
            return false;
        }
    }

    /**
     * Prepares the login
     *
     * Only implemented for openID
     */
    public function loginStart()
    {}

    /**
     * Logs the user in
     *
     * @param String $s_username            
     * @param String $s_password
     *            text password
     * @param Boolean $bo_autologin
     *            true for auto login
     * @return array id, username and password_expired if the login is correct, otherwise null
     */
    public function login($s_username, $s_password, $bo_autologin = false)
    {
        $s_password = $this->model_User->createUser()->hashPassword($s_password, $s_username);
        $i_tries = $this->model_User->registerLoginTries();
        if ($i_tries > 6) {
            /* Don't even check data */
            $this->service_Logs->loginLog($s_username, 'failed', $i_tries);
            return null;
        }
        
        $this->builder->select('users', 'id, nick,bot,active,blocked,password_expired,lastLogin');
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
            $a_data = null;
        } else {
            $a_data = $service_Database->fetch_assoc();
        }
        
        if ($a_data[0]['bot'] == '1' || $a_data[0]['active'] == '0' || $a_data[0]['blocked'] == '1') {
            $a_data = null;
        }
        
        if (is_null($a_data) || $i_tries >= 5) {
            if ($i_tries == 5) {
                $this->model_User->disableAccount($s_username);
                $this->model_PM->systemMessage('Account block', 'The account ' . $s_username . ' is disabled on ' . date('d-m-Y H:i:s') . ' after 3 failed login attempts.\n\n System');
            } else 
                if ($i_tries == 10) {
                    $this->builder->insert('ipban', 'ip', 's', $_SERVER['REMOTE_ADDR'])->getResult();
                    $this->model_PM->systemMessage('IP block', 'The IP ' . $_SERVER['REMOTE_ADDR'] . ' is blocked on ' . date('d-m-Y H:i:s') . ' after 6 failed login attempts. \n\n System');
                }
            
            $this->service_Logs->loginLog($s_username, 'failed', $i_tries);
            
            return null;
        }
        
        $this->model_User->clearLoginTries();
        $this->service_Logs->loginLog($s_username, 'success', $i_tries);
        
        unset($a_data[0]['bot']);
        unset($a_data[0]['active']);
        unset($a_data[0]['blocked']);
        
        if ($bo_autologin) {
            $this->builder->delete('autologin')
                ->getWhere()
                ->addAnd('userID', 'i', $a_data[0]['id']);
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
                $a_data[0]['id'],
                $a_data[0]['nick'],
                $a_data[0]['userType'],
                $_SERVER['REMOTE_ADDR']
            ));
            $service_Database = $this->builder->getResult();
            
            $a_data[0]['autologin'] = $service_Database->getID();
        }
        
        return $a_data[0];
    }

    /**
     * Logs the user out
     *
     * @param String $s_url
     *            The redirectUrl
     */
    public function logout($s_url)
    {
        header('location: ' . $s_url);
    }

    /**
     * Sends the activation email
     *
     * @param String $s_username
     *            username
     * @param String $s_email
     *            email address
     * @param String $s_registrationKey
     *            activation code
     * @throws ErrorException If the sending of the email failes
     */
    private function sendActivationEmail($s_username, $s_email, $s_registrationKey)
    {
        if (! $this->service_Mailer->registrationMail($s_username, $s_email, $s_registrationKey))
            throw new Exception("Sending registration mail to '.$s_email.' failed.");
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
    public function resendActivationEmail($s_username, $s_email)
    {
        $this->builder->select('users', 'nick,email,activation')
            ->getWhere()
            ->addAnd(array(
            'nick',
            'email',
            'activation'
        ), array(
            's',
            's',
            's'
        ), array(
            $s_username,
            $s_email,
            ''
        ), array(
            '=',
            '=',
            '<>'
        ));
        $service_Database = $this->builder->getResult();
        
        if ($service_Database->num_rows() == 0) {
            return false;
        }
        
        try {
            $a_data = $service_Database->fetch_assoc();
            $this->sendActivationEmail($a_data[0]['nick'], $a_data[0]['email'], $a_data[0]['activation']);
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}