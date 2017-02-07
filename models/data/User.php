<?php
namespace youconix\core\models\data;

/**
 * User data model
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
 */
class User extends \youconix\core\models\Equivalent
{

    /**
     *
     * @var \youconix\core\models\Groups
     */
    protected $groups;

    /**
     *
     * @var \Language
     */
    protected $obj_language;

    /**
     *
     * @var \youconix\core\services\Hashing
     */
    protected $hashing;

    /**
     *
     * @var int
     */
    protected $userid = null;

    protected $username = '';

    protected $email = '';

    /**
     *
     * @var int
     */
    protected $bot = 0;

    /**
     *
     * @var int
     */
    protected $registrated = 0;

    /**
     *
     * @var int
     */
    protected $loggedIn = 0;

    /**
     *
     * @var int
     */
    protected $active = 0;

    /**
     *
     * @var int
     */
    protected $blocked = 0;

    /**
     *
     * @var int
     */
    protected $passwordExpired = 0;

    /**
     *
     * @var string
     */
    protected $password;

    /**
     *
     * @var string
     */
    protected $profile = '';

    /**
     *
     * @var string
     */
    protected $activation = '';

    protected $a_levels = array();

    /**
     *
     * @var string
     */
    protected $loginType = 'normal';

    /**
     *
     * @var string
     */
    protected $language = '';

    /**
     *
     * @var int
     */
    protected $bindToIp = 0;
    
    /**
     *
     * @var int
     */
    protected $password_expired = 0;
    
    protected $salt = '';

    /**
     * PHP5 constructor
     *
     * @param \Builder $builder            
     * @param \Validation $validation            
     * @param \youconix\core\models\EquavalentHelper $helper
     * @param \youconix\core\services\Hashing $hashing            
     * @param \youconix\core\models\Groups $groups            
     * @param \Language $language            
     */
    public function __construct(\Builder $builder, \Validation $validation,\youconix\core\models\EquavalentHelper $helper, \youconix\core\services\Hashing $hashing, \youconix\core\models\Groups $groups, \Language $language)
    {
        parent::__construct($builder, $validation,$helper);
        $this->groups = $groups;
        $this->obj_language = $language;
        $this->hashing = $hashing;
	$this->s_table = 'users';
        
        $this->a_validation = array(
            'username' => 'type:string|required',
            'email' => 'type:string|required|pattern:email',
            'bot' => 'type:enum|required|set:0,1',
            'registrated' => 'type:int|required',
            'active' => 'type:enum|required|set:0,1',
            'blocked' => 'type:enum|required|set:0,1',
            'password' => 'type:string|required',
            'profile' => 'type:string',
            'activation' => 'type:string',
            'loginType' => 'type:string|required',
            'language' => 'type:string',
            'bindToIp' => 'type:enum|required|set:0,1',
	    'password_expired' => 'type:enum|required|set:0,1',
        );
    }

    /**
     * Collects the users userid, nick and level
     *
     * @param int $i_userid
     *            The userid
     * @throws \DBException If the userid is invalid
     */
    public function loadData($i_userid)
    {
        \youconix\core\Memory::type('int', $i_userid);
        
        $this->builder->select('users', '*')
            ->getWhere()
            ->bindInt('id', $i_userid);
        $database = $this->builder->getResult();
        
        if ($database->num_rows() == 0) {
            throw new \DBException("Unknown user with userid " . $i_userid);
        }
        
        $a_data = $database->fetch_object();
        
        $this->setData($a_data[0]);
    }

    /**
     * Sets the user data
     *
     * @param \stdClass $data
     *            user data
     */
    public function setData(\stdClass $data)
    {
        
        $this->userid = (int) $data->id;
        $this->username = $data->nick;
        $this->email = $data->email;
        $this->profile = $data->profile;
        $this->bot = (int) $data->bot;
        $this->registrated = (int) $data->registrated;
        $this->loggedIn = (int) $data->lastLogin;
        $this->active = (int) $data->active;
        $this->blocked = (int) $data->blocked;
        $this->loginType = $data->loginType;
        $this->language = $data->language;
        $this->passwordExpired = $data->password_expired;
        $this->bindToIp = $data->bindToIp;
	$this->password_expired = $data->password_expired;
        
        $s_systemLanguage = $this->obj_language->getLanguage();
        if (defined('USERID') && USERID == $this->userid && $this->obj_language != $s_systemLanguage) {
            if ($this->getLanguage() != $this->language) {
                $this->builder->update('users')
                    ->bindString('language', $s_systemLanguage)
                    ->getWhere()
                    ->bindInt('id', $this->userid);
                $this->builder->getResult();
            }
        }
    }

    /**
     * Returns the userid
     *
     * @return int
     */
    public function getID()
    {
        return $this->userid;
    }

    /**
     * Returns the username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Sets the username
     *
     * @param string $s_username            
     */
    public function setUsername($s_username)
    {
        \youconix\core\Memory::type('string', $s_username);
        $this->username = $s_username;
    }

    /**
     * Returns the email address
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Sets the email address
     *
     * @param string $s_email            
     */
    public function setEmail($s_email)
    {
        \youconix\core\Memory::type('string', $s_email);
        $this->email = $s_email;
    }

    /**
     * Sets a new password
     *
     * @param string $s_password
     *            plain text password
     * @param boolean $bo_expired
     *            true to set the password to expired
     */
    public function setPassword($s_password, $bo_expired = false)
    {
        \youconix\core\Memory::type('string', $s_password);
        
	$this->password = $this->hashing->hash($s_password);
	if( $bo_expired ){
	  $this->password_expired = 1;
	}
    }

    /**
     * Changes the saved password
     *
     * @param string $s_passwordOld
     *            plain text password
     * @param string $s_password
     *            plain text password
     * @return bool True if the password is changed
     */
    public function changePassword($s_passwordOld, $s_password)
    {        
        $s_passwordOld = $this->hashing->hash($s_passwordOld);
        $s_password = $this->hashing->hash($s_password);
        
        $this->builder->select('users', 'id')
            ->getWhere()
            ->bindInt('id', $this->getID())
            ->bindString('password', $s_passwordOld);
        $database = $this->builder->getResult();
        
        if ($database->num_rows() == 0) {
            return false;
        }
        
        $i_userid = $database->result(0, 'id');
        
        $this->builder->update('users')
            ->bindString('password', $s_password)
            ->bindString('password_expired', '0')
            ->getWhere()
            ->bindInt('id', $i_userid);
        $this->builder->getResult();
        
        return true;
    }

    /**
     * Checks if the user is a system account
     *
     * @return boolean if the user is a system account
     */
    public function isBot()
    {
        return ($this->bot == 1);
    }

    /**
     * Sets the account as a normal or system account
     *
     * @param boolean $bo_bot
     *            to true for a system account
     */
    public function setBot($bo_bot)
    {
        \youconix\core\Memory::type('boolean', $bo_bot);
        
        if ($bo_bot) {
            $this->bot = 1;
        } else {
            $this->bot = 0;
        }
    }

    /**
     *
     * @return boolean
     */
    public function isPasswordExpired()
    {
        return ($this->passwordExpired == 1);
    }

    /**
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return ($this->active == 1);
    }

    /**
     * *
     *
     * @return int registration date as a timestamp
     */
    public function getRegistrated()
    {
        return $this->registrated;
    }

    /**
     *
     * @return int The logged in date as a timestamp
     */
    public function lastLoggedIn()
    {
        return $this->loggedIn;
    }

    /**
     * Updates the last login date
     */
    public function updateLastLoggedIn()
    {
        $i_time = time();
        $this->loggedIn = $i_time;
        
        $this->builder->update('users')
            ->bindInt('lastLogin', $i_time)
            ->getWhere()
            ->bindInt('id', $this->getID());
        $this->builder->getResult();
    }

    /**
     *
     * @return boolean
     */
    public function isBlocked()
    {
        return ($this->blocked == 1);
    }

    /**
     * (Un)Blocks the account
     *
     * @param boolean $bo_blocked
     *            to true to block the account, otherwise false
     */
    public function setBlocked($bo_blocked)
    {
        \youconix\core\Memory::type('boolean', $bo_blocked);
        
        if ($bo_blocked) {
            $this->blocked = 1;
        } else {
            $this->blocked = 0;
        }
    }

    /**
     * Sets the activation code
     *
     * @param string $s_activation            
     */
    public function setActivation($s_activation)
    {
        $this->activation = $s_activation;
    }

    /**
     * Returns the activation code
     *
     * @return string
     */
    public function getActivation()
    {
        return $this->activation;
    }

    /**
     * Returns the profile text
     *
     * @return string
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Sets the profile text
     *
     * @param string $s_text            
     */
    public function setProfile($s_profile)
    {
        $this->profile = $s_profile;
    }

    /**
     * Returns the groups where the user is in
     *
     * @return arrays The groups
     */
    public function getGroups()
    {
        $a_groups = $this->groups->getGroups();
        $a_groupsUser = array();
        
        foreach ($a_groups as $obj_group) {
            $i_level = $obj_group->getLevelByGroupID($this->userid);
            
            if ($i_level != \Session::ANONYMOUS) {
                $a_groupsUser[$obj_group->getID()] = $i_level;
            }
        }
        
        return $a_groupsUser;
    }

    /**
     * Returns the access level for the current group
     *
     * @return int access level
     */
    public function getLevel($i_groupid = -1)
    {
        $i_groupid = $this->checkGroup($i_groupid);
        
        if (array_key_exists($i_groupid, $this->a_levels)) {
            return $this->a_levels[$i_groupid];
        }
        if (is_null($this->userid)) {
            return \Session::ANONYMOUS;
        }
        
        $this->a_levels[$i_groupid] = $this->groups->getLevel($this->userid, $i_groupid);
        return $this->a_levels[$i_groupid];
    }

    /**
     * Disables the user account
     */
    public function disableAccount()
    {
        $this->active = 0;
    }

    /**
     * Enabled the user account
     */
    public function enableAccount()
    {
        $this->active = 1;
    }

    /**
     * Returns the color corosponding the users level
     *
     * @param int $i_groupid
     *            The groupid, leave empty for site group
     * @return string The color
     */
    public function getColor($i_groupid = -1)
    {
        \youconix\core\Memory::type('int', $i_groupid);
        
        $i_group = $this->checkGroup($i_groupid);
        
        switch ($this->getLevel($i_group)) {
            case \Session::ANONYMOUS:
                return \Session::ANONYMOUS_COLOR;
            
            case \Session::USER:
                return \Session::USER_COLOR;
            
            case \Session::MODERATOR:
                return \Session::MODERATOR_COLOR;
            
            case \Session::ADMIN:
                return \Session::ADMIN_COLOR;
        }
    }

    /**
     * Checks is the visitor has moderator rights
     *
     * @param int $i_groupid
     *            The group ID, leave empty for site group
     * @return boolean True if the visitor has moderator rights, otherwise false
     */
    public function isModerator($i_groupid = -1)
    {
        \youconix\core\Memory::type('int', $i_groupid);
        
        $i_groupid = $this->checkGroup($i_groupid);
        
        return ($this->getLevel($i_groupid) >= \Session::MODERATOR);
    }

    /**
     * Checks is the visitor has administrator rights
     *
     * @param int $i_groupid
     *            The group ID, leave empty for site group
     * @return boolean True if the visitor has administrator rights, otherwise false
     */
    public function isAdmin($i_groupid = -1)
    {
        \youconix\core\Memory::type('int', $i_groupid);
        
        $i_groupid = $this->checkGroup($i_groupid);
        
        return ($this->getLevel($i_groupid) >= \Session::ADMIN);
    }

    /**
     * Checks the group ID
     *
     * @param int $i_groupid
     *            groupID, may be -1 for site group
     * @return int group ID
     */
    protected function checkGroup($i_groupid)
    {
        if ($i_groupid == - 1) {
            $i_groupid = GROUP_SITE;
        }
        
        return $i_groupid;
    }

    /**
     * Sets the password as expired
     * Forcing the user to change the password
     */
    public function expirePassword()
    {
        $this->builder->update('users')
            ->bindstring('password_expires', '1')
            ->getWhere()
            ->bindInt('id', $this->userid);
        $this->builder->getResult();
    }

    /**
     * Returns the set user language
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Returns the login type
     *
     * @return string
     */
    public function getLoginType()
    {
        return $this->loginType;
    }

    /**
     * Sets the login type
     *
     * @return string type
     */
    public function setLoginType($s_type)
    {
        $this->loginType = $s_type;
    }
    
    public function setBindToIp($bo_bind){
        $this->bindToIp = 0;
        if( $bo_bind ){
            $this->bindToIp = 1;
        }
    }
    
    public function isBindedToIp(){
        return ($this->bindToIp == 1);
    }

    /**
     * Saves the user
     */
    public function save()
    {
        if (is_null($this->id)) {
            $this->add();
        } else {
            $this->update();
        }
        
        $this->password = '';
    }

    /**
     * Adds the new user in the database
     */
    protected function add()
    {
        $this->performValidation();
        
        $this->registrated = time();
        
        $this->builder->insert('users')
            ->bindString('nick', $this->username)
            ->bindString('email', $this->email)
            ->bindString('password', $this->password)
            ->bindString('bot', $this->bot)
            ->bindInt('registrated', $this->registrated)
            ->bindInt('lastLogin', $this->loggedIn)
            ->bindString('active', $this->active)
            ->bindString('activation', $this->activation)
            ->bindString('profile', $this->profile)
            ->bindString('loginType', $this->loginType)
            ->bindInt('bindToIp',$this->bindToIp)
	    ->bindString('password_expired',$this->password_expired)
	    ->bindString('salt',$this->salt);
        
        $this->userid = (int) $this->builder->getResult()->getId();
        
        if ($this->userid == - 1) {
            return;
        }
        
        $this->groups->addUserDefaultGroups($this->userid);
    }

    /**
     * Saves the changed user in the database
     */
    protected function update()
    {
        $this->password = 'adklshjakbsdas'; // for validation
        $this->performValidation();
        
        $this->builder->update('users')
            ->bindString('nick', $this->username)
            ->bindString('email', $this->email)
            ->bindString('bot', $this->bot)
            ->bindString('active', $this->active)
            ->bindString('blocked', $this->blocked)
            ->bindString('profile', $this->profile)
            ->bindInt('bindToIp',$this->bindToIp);
	
	if( !empty($this->password) ){
	  $this->builder->bindString('password',$this->password)->bindString('password_expired',$this->password_expired);
	}
        $this->builder->getWhere()->bindInt('id', $this->userid)->getResult();
    }

    /**
     * Deletes the user permantly
     */
    public function delete()
    {
        if (is_null($this->userid)) {
            return;
        }
        
        /* Delete user from groups */
        $this->groups->deleteUserFromGroups($this->userid);
        
        $this->builder->delete('users')
            ->getWhere()
            ->bindInt('id', $this->userid);
        $this->builder->getResult();
        $this->userid = null;
    }

    public function __get($s_key){
        switch($s_key){
          case 'nick' :
            return $this->getUsername();
        }
        return parent::__get($s_key);
    }

    public function __set($s_key,$s_value){
        switch($s_key){
          case 'nick' :
            $this->setUsername($s_value);
            break;

          default :
            parent::__set($s_key, $s_value);
        }
    }
}
