<?php
namespace core\models;

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
 * User data model.
 * Contains the user data
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
 */
class User extends \core\models\Model
{

    protected $a_userModels;

    /**
     * 
     * @var \core\models\data\User
     */
    protected $userData;

    /**
     * PHP5 constructor
     *
     * @param \Builder $builder
     * @param \Validation $validation
     * @param \core\models\data\User $userData
     */
    public function __construct(\Builder $builder, \Validation $validation, \core\models\data\User $userData)
    {
        parent::__construct($builder,$validation);
        
        $this->a_userModels = array();
        $this->userData = $userData;
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
     * Gets the requested users
     *
     * @param array $a_userid
     *            Array from user IDs
     * @return User array The data objects
     */
    public function getUsersById($a_userid)
    {
        \core\Memory::type('array', $a_userid);
        
        $a_users = array();
        $this->builder->select('users', '*')
            ->getWhere()
            ->addAnd('id', 'i', array(
            0 => $a_userid
        ), 'IN');
        $service_Database = $this->builder->getResult();
        
        if ($service_Database->num_rows() > 0) {
            $a_data = $service_Database->fetch_assoc();
            
            foreach ($a_data as $a_user) {
                $i_userid = (int) $a_user['id'];
                
                if (array_key_exists($i_userid, $this->a_userModels)) {
                    $a_users[$i_userid] = $this->a_userModels[$i_userid];
                } else {
                    $obj_User = $this->userData->cloneModel();
                    $obj_User->loadData($i_userid);
                    $a_users[$i_userid] = $obj_User;
                }
            }
        }
        return $a_users;
    }

    /**
     * Gets the requested user
     *
     * @param int $i_userid
     *            The userid, leave empty for logged in user
     * @return \core\models\data\User The user object of a empty data object if the user is not logged in
     * @throws DBException If the userid is invalid
     */
    public function get($i_userid = -1)
    {
        $i_userid = (int) $this->checkUserid($i_userid);
        
        if ($i_userid == - 1) {
            return $this->userData->cloneModel();
        }
        
        if (array_key_exists($i_userid, $this->a_userModels)) {
            return $this->a_userModels[$i_userid];
        }
        
        $obj_User = $this->userData->cloneModel();
        $obj_User->loadData($i_userid);
        $this->a_userModels[$i_userid] = $obj_User;
        
        return $this->a_userModels[$i_userid];
    }
    
    /**
     * Returns the user with the given username and email
     * 
     * @param string $s_username    The username
     * @param string $s_email   The email address
     * @return \core\models\Model   The user object or null if the user does not exist
     */
    public function getByName($s_username,$s_email = ''){
        $this->builder->select('users','*')->getWhere()->addAnd(array('username','active','blocked'),array('s','s','s'),
            array($s_username,'1','0'));
        if( !empty($s_email) ){
            $this->builder->getWhere()->addAnd('email','s',$s_email);
        }
        $database = $this->builder->getResult();
        if( $database->num_rows() == 0 ){
            return null;
        }
        
        $a_data = $database->fetch_assoc();
        $obj_User = $this->userData->cloneModel();
        $obj_User->setData($a_data[0]);
        return $obj_User;
    }

    /**
     * Checks the userid
     *
     * @param int $i_userid
     *            userid, may be -1 for current user
     * @return int userid
     */
    private function checkUserid($i_userid)
    {
        if ($i_userid == - 1 && defined('USERID')) {
            $i_userid = USERID;
        }
        
        return (int) $i_userid;
    }

    /**
     * Gets 25 of the users sorted on nick.
     * Start from the given position, default 0
     *
     * @param int $i_start
     *            The startposition for the search, default 0
     * @return array The users
     */
    public function getUsers($i_start = 0)
    {
        \core\Memory::type('int', $i_start);
        
        $this->builder->select('users', '*')
            ->order('nick', 'ASC')
            ->limit(25, $i_start);
        $service_Database = $this->builder->getResult();
        
        $a_users = $service_Database->fetch_assoc();
        $a_result = array(
            'number' => 0,
            'data' => array()
        );
        
        foreach ($a_users as $a_user) {
            $obj_User = $this->userData->cloneModel();
            $obj_User->setData($a_user);
            $a_result['data'][] = $obj_User;
        }
        
        $this->builder->select('users', $this->builder->getCount('id', 'amount'));
        $a_result['number'] = $this->builder->getResult()->result(0, 'amount');
        
        return $a_result;
    }

    /**
     * Searches the user(s)
     * Limitated on 25 results
     *
     * @param string $s_username
     *            username to search on
     * @return array The users
     */
    public function searchUser($s_username)
    {
        \core\Memory::type('string', $s_username);
        
        $this->builder->select('users', '*')
            ->order('nick', 'ASC')
            ->limit(25)
            ->getWhere()
            ->addOr(array(
            'nick',
            'email'
        ), array(
            's',
            's'
        ), array(
            $s_username,
            $s_username
        ), array(
            'LIKE',
            'LIKE'
        ));
        
        $a_users = $this->builder->getResult()->fetch_assoc();
        $a_result = array(
            'number' => 0,
            'data' => array()
        );
        
        foreach ($a_users as $a_user) {
            $obj_User = $this->userData->cloneModel();
            $obj_User->setData($a_user);
            $a_result['data'][] = $obj_User;
        }
        
        return $a_result;
    }
    
    /**
     * Returns the user salt 
     * 
     * @see \core\models\data\User::getSalt()
     * @param string $s_username    The username
     * @param string $s_loginType   The login type
     * @return NULL|string  The salt if the user exists
     */
    public function getSalt($s_username,$s_loginType){
        return $this->userData->getSalt($s_username,$s_loginType);
    }
    
    /**
     * Activates the user
     *
     * @param string $s_code
     *            The activation code
     * @return boolean True if the user is activated
     * @throws Exception If activating the user failes
     */
    public function activate($s_code){
        $this->builder->select('users', 'id')
        ->getWhere()
        ->addAnd('activation', 's', $s_code);
        $service_Database = $this->builder->getResult();
        if ($service_Database->num_rows() == 0)
            return false;
        
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
            throw $e;
        }
    }

    /**
     * Creates a new user object
     *
     * @return \core\models\data\User The user object
     */
    public function createUser()
    {
        return $this->userData->cloneModel();
    }

    /**
     * Checks if the username is available
     *
     * @param String $s_username
     *            The username to check
     * @param int $i_userid
     *            The userid who to exclude, -1 for ignore
     * @param String $s_type
     *            login type, default normal
     * @return boolean if the username is available
     */
    public function checkUsername($s_username, $i_userid = -1, $s_type = 'normal')
    {
        \core\Memory::type('string', $s_username);
        \core\Memory::type('int', $i_userid);
        \core\Memory::type('string', $s_type);
        
        if ($i_userid != - 1) {
            $this->builder->select('users', 'id')
                ->getWhere()
                ->addAnd(array(
                'nick',
                'loginType',
                'id'
            ), array(
                's',
                's',
                'i'
            ), array(
                $s_username,
                $s_type,
                $i_userid
            ), array(
                '=',
                '=',
                '<>'
            ));
        } else {
            $this->builder->select('users', 'id')
                ->getWhere()
                ->addAnd(array(
                'nick',
                'loginType'
            ), array(
                's',
                's'
            ), array(
                $s_username,
                $s_type
            ));
        }
        
        $service_Database = $this->builder->getResult();
        if ($service_Database->num_rows() != 0) {
            return false;
        }
        
        return true;
    }

    /**
     * Checks or the given email address is availabel
     *
     * @param String $s_email
     *            The email address to check
     * @param int $i_userid
     *            The userid who to exclude, -1 for ignore
     * @return Boolean True if the email address is available
     */
    public function checkEmail($s_email, $i_userid = -1)
    {
        \core\Memory::type('string', $s_email);
        \core\Memory::type('int', $i_userid);
        
        if ($i_userid != - 1) {
            $this->builder->select('users', 'id')
                ->getWhere()
                ->addAnd(array(
                'email',
                'id'
            ), array(
                's',
                'i'
            ), array(
                $s_email,
                $i_userid
            ), array(
                '=',
                '<>'
            ));
        } else {
            $this->builder->select('users', 'id')
                ->getWhere()
                ->addAnd('email', 's', $s_email);
        }
        
        $service_Database = $this->builder->getResult();
        if ($service_Database->num_rows() != 0) {
            return false;
        }
        
        return true;
    }

    /**
     * Returns the site admins (control panel)
     *
     * @return Array The admins
     */
    public function getSiteAdmins()
    {
        $this->builder->select('users u', 'u.id,u.nick')->innerJoin('group_users g', 'u.id', 'g.userid');
        $this->builder->order('u.nick')
            ->getWhere()
            ->addAnd('g.groupID', 'i', 0);
        $service_Database = $this->builder->getResult();
        
        return $service_Database->fetch_assoc();
    }

    /**
     * Gets the id from all the activated users
     *
     * @return array ID's
     */
    public function getUserIDs()
    {
        $this->builder->select('users', 'id')
            ->getWhere()
            ->addAnd(array(
            'active',
            'blocked'
        ), array(
            's',
            's'
        ), array(
            '1',
            '0'
        ));
        $service_Database = $this->builder->getResult();
        
        $a_users = $service_Database->fetch_assoc();
        
        return $a_users;
    }
}