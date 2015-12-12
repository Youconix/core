<?php
namespace\core\services\data;

class AuthorizationLDAP extends AuthorizationNormal
{

    private $service_LDAP;

    private $service_Logs;


    private $model_User;

    private $model_PM;

    public function __construct(\core\services\LDAP $service_LDAP, \Builder $builder, \Logger $service_Logs, \core\models\User $model_User, \core\models\PM $model_PM)
    {
        parent::__construct($builder, $service_Logs, $model_User, $model_PM);
        $this->service_LDAP = $service_LDAP;
    }

    public function activateUser($s_code)
    {}

    public function login($s_username, $s_password, $bo_autologin = false)
    {
        $i_tries = $this->model_User->registerLoginTries();
        if ($i_tries > 6) {
            /* Don't even check data */
            $this->service_Logs->loginLog($s_username, 'failed', $i_tries);
            return null;
        }
        
        $a_data = null;
        try {
            $this->service_LDAP->bind($s_username, $s_password);
            $this->service_LDAP->unbind();
            
            $this->builder->select('users', 'id, nick,bot,active,blocked,password_expired,lastLogin');
            $this->builder->getWhere()->addAnd(array(
                'nick',
                'active',
                'loginType'
            ), array(
                's',
                's',
                's'
            ), array(
                $s_username,
                '1',
                'LDAP'
            ));
            $service_Database = $this->builder->getResult();
            
            if ($service_Database->num_rows() > 0) {
                $a_data = $service_Database->fetch_assoc();
                if ($a_data[0]['bot'] == '1' || $a_data[0]['active'] == '0' || $a_data[0]['blocked'] == '1') {
                    $a_data = null;
                }
            }
        } catch (\LdapException $e) {
            /* Server error */
            \core\Memory::services('ErrorHandler')->error($e);
            return false;
        } catch (\LdapConnectionException $e) {
            /* Login incorrect */
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
                'IP'
            ), array(
                'i',
                's',
                's'
            ), array(
                $a_data[0]['id'],
                $a_data[0]['nick'],
                $_SERVER['REMOTE_ADDR']
            ));
            $service_Database = $this->builder->getResult();
            
            $a_data[0]['autologin'] = $service_Database->getID();
        }
        
        return $a_data[0];
    }

    public function register($a_data, $bo_skipActivation = false)
    {
        try {
            $this->service_LDAP->bind($a_data['username'], $a_data['password']);
            $this->service_LDAP->unbind();
        } catch (\LdapException $e) {
            /* Server error */
            \core\Memory::services('ErrorHandler')->error($e);
            return false;
        } catch (\LdapConnectionException $e) {
            /* Login incorrect */
            return false;
        }
        
        return parent::register($a_data, true);
    }
}