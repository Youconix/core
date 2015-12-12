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
class User extends \youconix\core\models\Model
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
    protected $language;

    /**
     *
     * @var \youconix\core\services\Hashing
     */
    protected $hashing;

    protected $i_userid = null;

    protected $s_username = '';

    protected $s_email = '';

    protected $i_bot = 0;

    protected $i_registrated = 0;

    protected $i_loggedIn = 0;

    protected $i_active = 0;

    protected $i_blocked = 0;

    protected $i_passwordExpired = 0;

    protected $s_password;

    protected $s_profile = '';

    protected $s_activation = '';

    protected $a_levels = array();

    protected $s_loginType;

    protected $s_language = '';

    protected $i_bindToIp = false;

    /**
     * PHP5 constructor
     *
     * @param \Builder $builder            
     * @param \Validation $validation            
     * @param \youconix\core\services\Hashing $hashing            
     * @param \youconix\core\models\Groups $groups            
     * @param \Language $language            
     */
    public function __construct(\Builder $builder, \Validation $validation, \youconix\core\services\Hashing $hashing, \youconix\core\models\Groups $groups, \Language $language)
    {
        parent::__construct($builder, $validation);
        $this->groups = $groups;
        $this->language = $language;
        $this->hashing = $hashing;
        
        $this->a_validation = array(
            's_username' => array(
                'type' => 'string',
                'required' => 1
            ),
            's_email' => array(
                'type' => 'string',
                'required' => 1,
                'pattern' => 'email'
            ),
            'i_bot' => array(
                'type' => 'enum',
                'set' => array(
                    0,
                    1
                )
            ),
            'i_registrated' => array(
                'type' => 'enum',
                'set' => array(
                    0,
                    1
                )
            ),
            'i_active' => array(
                'type' => 'enum',
                'set' => array(
                    0,
                    1
                )
            ),
            'i_blocked' => array(
                'type' => 'enum',
                'set' => array(
                    0,
                    1
                )
            ),
            's_password' => array(
                'type' => 'string',
                'required' => 1
            ),
            's_profile' => array(
                'type' => 'string'
            ),
            's_activation' => array(
                'type' => 'string'
            ),
            's_loginType' => array(
                'type' => 'string',
                'required' => 1
            ),
            's_language' => array(
                'type' => 'string'
            )
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
            ->addAnd('id', 'i', $i_userid);
        $service_Database = $this->builder->getResult();
        
        if ($service_Database->num_rows() == 0) {
            throw new \DBException("Unknown user with userid " . $i_userid);
        }
        
        $a_data = $service_Database->fetch_assoc();
        
        $this->setData($a_data[0]);
    }

    /**
     * Sets the user data
     *
     * @param array $a_data
     *            user data
     */
    public function setData($a_data)
    {
        \youconix\core\Memory::type('array', $a_data);
        
        $this->i_userid = (int) $a_data['id'];
        $this->s_username = $a_data['nick'];
        $this->s_email = $a_data['email'];
        $this->s_profile = $a_data['profile'];
        $this->i_bot = (int) $a_data['bot'];
        $this->i_registrated = (int) $a_data['registrated'];
        $this->i_loggedIn = (int) $a_data['lastLogin'];
        $this->i_active = (int) $a_data['active'];
        $this->i_blocked = (int) $a_data['blocked'];
        $this->s_loginType = $a_data['loginType'];
        $this->s_language = $a_data['language'];
        $this->i_passwordExpired = $a_data['password_expired'];
        
        $s_systemLanguage = $this->language->getLanguage();
        if (defined('USERID') && USERID == $this->i_userid && $this->s_language != $s_systemLanguage) {
            if ($this->getLanguage() != $this->s_language) {
                $this->builder->update('users', 'language', 's', $s_systemLanguage)
                    ->getWhere()
                    ->addAnd('id', 'i', $this->i_userid);
                $this->builder->getResult();
            }
        }
    }

    /**
     * Returns the userid
     *
     * @return int The userid
     */
    public function getID()
    {
        return $this->i_userid;
    }

    /**
     * Returns the username
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->s_username;
    }

    /**
     * Sets the username
     *
     * @param string $s_username
     *            The new username
     */
    public function setUsername($s_username)
    {
        \youconix\core\Memory::type('string', $s_username);
        $this->s_username = $s_username;
    }

    /**
     * Returns the email address
     *
     * @return string The email address
     */
    public function getEmail()
    {
        return $this->s_email;
    }

    /**
     * Sets the email address
     *
     * @param string $s_email
     *            email address
     */
    public function setEmail($s_email)
    {
        \youconix\core\Memory::type('string', $s_email);
        $this->s_email = $s_email;
    }

    /**
     * Sets a new password
     * Note : username has to be set first!
     *
     * @param string $s_password
     *            plain text password
     * @param boolean $bo_expired
     *            true to set the password to expired
     */
    public function setPassword($s_password, $bo_expired = false)
    {
        \youconix\core\Memory::type('string', $s_password);
        
        $s_salt = $this->getSalt($this->getUsername(), $this->s_loginType);
        
        $this->s_password = $this->hashing->hashUserPassword($s_password, $s_salt);
        
        if ($bo_expired) {
            $this->builder->update('users', array(
                'password',
                'password_expired'
            ), array(
                's',
                's'
            ), array(
                $this->s_password,
                '1'
            ));
            $this->builder->getWhere()->addAnd('id', 'i', $this->i_userid);
            $this->builder->getResult();
        } else {
            $this->builder->update('users', 'password', 's', $this->s_password)
                ->getWhere()
                ->addAnd('id', 'i', $this->i_userid);
            $this->builder->getResult();
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
        $s_salt = $this->getSalt($this->getUsername(), $this->s_loginType);
        if (is_null($s_salt)) {
            return false;
        }
        
        $s_passwordOld = $this->hashing->hashUserPassword($s_passwordOld, $s_salt);
        $s_password = $this->hashing->hashUserPassword($s_password, $s_salt);
        
        $this->builder->select('users', 'id')
            ->getWhere()
            ->addAnd(array(
            'id',
            'password'
        ), array(
            'i',
            's'
        ), array(
            $this->getID(),
            $s_passwordOld
        ));
        $service_Database = $this->builder->getResult();
        
        if ($service_Database->num_rows() == 0) {
            return false;
        }
        
        $i_userid = $service_Database->result(0, 'id');
        
        $this->builder->update('users', array(
            'password',
            'password_expired'
        ), array(
            's',
            's'
        ), array(
            $s_password,
            '0'
        ));
        $this->builder->getWhere()->addAnd('id', 'i', $i_userid);
        $this->builder->getResult();
        
        return true;
    }

    /**
     * Returns the user salt
     *
     * @param string $s_username
     *            The username
     * @param string $s_loginType
     *            The login type
     * @return NULL|string The salt if the user exists
     */
    public function getSalt($s_username, $s_loginType)
    {
        $this->builder->select('users', 'salt,id')
            ->getWhere()
            ->addAnd(array(
            'nick',
            'active',
            'loginType'
        ), array(
            's',
            's',
            's',
            's'
        ), array(
            $s_username,
            '1',
            $s_loginType
        ));
        $service_Database = $this->builder->getResult();
        
        if ($service_Database->num_rows() == 0) {
            return null;
        }
        
        $a_data = $service_Database->fetch_assoc();
        
        if (empty($a_data[0]['salt'])) {
            $s_salt = $this->hashing->createSalt();
            $this->builder->update('users', 'salt', 's', $s_salt)->getWhere('id', 'i', $a_data[0]['id']);
            $this->builder->getResult();
            
            return $s_salt;
        }
        
        return $a_data[0]['salt'];
    }

    /**
     * Checks if the user is a system account
     *
     * @return boolean if the user is a system account
     */
    public function isBot()
    {
        return ($this->i_bot == 1);
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
            $this->i_bot = 1;
        } else {
            $this->i_bot = 0;
        }
    }

    /**
     *
     * @return boolean
     */
    public function isPasswordExpired()
    {
        return ($this->i_passwordExpired == 1);
    }

    /**
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return ($this->i_active == 1);
    }

    /**
     * *
     * 
     * @return int registration date as a timestamp
     */
    public function getRegistrated()
    {
        return $this->i_registrated;
    }

    /**
     *
     * @return int The logged in date as a timestamp
     */
    public function lastLoggedIn()
    {
        return $this->i_loggedIn;
    }

    /**
     * Updates the last login date
     */
    public function updateLastLoggedIn()
    {
        $i_time = time();
        $this->i_loggedIn = $i_time;
        
        $this->builder->update('users', 'lastLogin', 'i', $i_time)
            ->getWhere()
            ->addAnd('id', 'i', $this->getID());
        $this->builder->getResult();
    }

    /**
     *
     * @return boolean
     */
    public function isBlocked()
    {
        return ($this->i_blocked == 1);
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
            $this->i_blocked = 1;
        } else {
            $this->i_blocked = 0;
        }
    }

    /**
     * Sets the activation code
     *
     * @param string $s_activation            
     */
    public function setActivation($s_activation)
    {
        $this->s_activation = $s_activation;
    }

    /**
     * Returns the activation code
     *
     * @return string The code
     */
    public function getActivation()
    {
        return $this->s_activation;
    }

    /**
     * Returns the profile text
     *
     * @return string text
     */
    public function getProfile()
    {
        return $this->s_profile;
    }

    /**
     * Sets the profile text
     *
     * @param string $s_text
     *            text
     */
    public function setProfile($s_profile)
    {
        $this->s_profile = $s_profile;
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
            $i_level = $obj_group->getLevelByGroupID($this->i_userid);
            
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
        if (is_null($this->i_userid)) {
            return \Session::ANONYMOUS;
        }
        
        $this->a_levels[$i_groupid] = $this->groups->getLevel($this->i_userid, $i_groupid);
        return $this->a_levels[$i_groupid];
    }

    /**
     * Disables the user account
     */
    public function disableAccount()
    {
        $this->i_active = 0;
    }

    /**
     * Enabled the user account
     */
    public function enableAccount()
    {
        $this->i_active = 1;
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
        $this->builder->update('users', 'password_expires', 's', 'i')
            ->getWhere()
            ->addAnd('id', 'i', $this->i_userid);
        $this->builder->getResult();
    }

    /**
     * Returns the set user language
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->s_language;
    }

    /**
     * Returns the login type
     *
     * @return string
     */
    public function getLoginType()
    {
        return $this->s_loginType;
    }

    /**
     * Sets the login type
     *
     * @return string type
     */
    public function setLoginType($s_type)
    {
        $this->s_loginType = $s_type;
    }

    /**
     * Saves the new user in the database
     */
    public function save()
    {
        if (! is_null($this->i_userid)) {
            $this->persist();
            return;
        }
        
        $this->performValidation();
        
        $this->i_registrated = time();
        
        $this->builder->insert('users', array(
            'nick',
            'email',
            'password',
            'bot',
            'registrated',
            'lastLogin',
            'active',
            'activation',
            'profile',
            'loginType'
        ), array(
            's',
            's',
            's',
            's',
            'i',
            'i',
            's',
            's',
            's',
            's'
        ), array(
            $this->s_username,
            $this->s_email,
            $this->s_password,
            $this->i_bot,
            $this->i_registrated,
            $this->i_loggedIn,
            $this->i_active,
            $this->s_activation,
            $this->s_profile,
            $this->s_loginType
        ));
        
        $this->i_userid = (int) $this->builder->getResult()->getId();
        
        if ($this->i_userid == - 1) {
            return;
        }
        
        $this->groups->addUserDefaultGroups($this->i_userid);
    }

    /**
     * Saves the changed user in the database
     */
    public function persist()
    {
        if (is_null($this->i_userid)) {
            $this->save();
            return;
        }
        
        $this->performValidation();
        
        $this->builder->update('users', array(
            'nick',
            'email',
            'bot',
            'active',
            'blocked',
            'profile'
        ), array(
            's',
            's',
            's',
            's',
            's',
            's'
        ), array(
            $this->s_username,
            $this->s_email,
            $this->i_bot,
            $this->i_active,
            $this->i_blocked,
            $this->s_profile
        ));
        $this->builder->getWhere()->addAnd('id', 'i', $this->i_userid);
        $this->builder->getResult();
    }

    /**
     * Deletes the user permantly
     */
    public function delete()
    {
        if (is_null($this->i_userid)) {
            return;
        }
        
        /* Delete user from groups */
        $this->groups->deleteGroupsUser($this->i_userid);
        
        $this->builder->delete('users')
            ->getWhere()
            ->addAnd('id', 'i', $this->i_userid);
        $this->builder->getResult();
        $this->i_userid = null;
    }
}