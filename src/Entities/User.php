<?php

namespace youconix\Core\Entities;

/**
 * @Table(name="users")
 * @ORM\Entity(repositoryClass="youconix\Core\repositories\User")
 */
class User extends \youconix\Core\ORM\AbstractEntity
{

  /**
   *
   * @ManyToOne(targetEntity="UserGroup")
   * @JoinColumn(name="userid", referencedColumnName="userid")
   */
  protected $groups;

  /**
   *
   * @var \LanguageInterface
   */
  protected $obj_language;

  /**
   *
   * @var \youconix\Core\Services\Hashing
   */
  protected $hashing;

  /**
   *
   * @Id
   * @GeneratedValue
   * @Column(type="integer", name="id")
   */
  protected $userid = null;

  /**
   *
   * @Column(type="string", name="nick")
   */
  protected $username = '';

  /**
   *
   * @Column(type="string")
   */
  protected $email = '';

  /**
   *
   * @Column(type="boolean")
   */
  protected $bot = 0;

  /**
   *
   * @Column(type="datetime")
   */
  protected $registrated;

  /**
   *
   * @Column(type="datetime")
   */
  protected $lastLogin;

  /**
   *
   * @Column(type="boolean", name="active")
   */
  protected $enabled = 0;

  /**
   *
   * @Column(type="boolean")
   */
  protected $blocked = 0;

  /**
   *
   * @Column(type="boolean", name="password_expired")
   */
  protected $passwordExpired = 0;

  /**
   *
   * @Column(type="string")
   */
  protected $password;

  /**
   *
   * @Column(type="string")
   */
  protected $profile = '';

  /**
   *
   * @Column(type="string")
   */
  protected $activation = '';

  /**
   *
   * @Column(type="string")
   */
  protected $loginType;

  /**
   *
   * @Column(type="string")
   */
  protected $language = '';

  /**
   *
   * @Column(type="boolean")
   */
  protected $bindToIp = 0;

  /**
   * PHP5 constructor
   *
   * @param \Builder $builder            
   * @param \Validation $validation
   * @param \youconix\Core\Services\Hashing $hashing
   * @param \LanguageInterface $language            
   */
  public function __construct(\BuilderInterface $builder, \Validation $validation,
			      \youconix\Core\Services\Hashing $hashing, \LanguageInterface $language)
  {
    parent::__construct($builder, $validation);

    $this->obj_language = $language;
    $this->hashing = $hashing;

    $this->a_validation = array(
	'username' => 'type:string|required',
	'email' => 'type:string|required|pattern:email',
	'bot' => 'type:enum|required|set:0,1',
	'registrated' => 'type:datetime|required',
	'active' => 'type:enum|required|set:0,1',
	'blocked' => 'type:enum|required|set:0,1',
	'profile' => 'type:string',
	'activation' => 'type:string',
	'loginType' => 'type:string|required',
	'LanguageInterface' => 'type:string',
	'bindToIp' => 'type:enum|required|set:0,1',
	'passwordExpired' => 'type:enum|required|set:0,1',
    );
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

    $s_systemLanguage = $this->obj_language->getLanguage();
    if (defined('USERID') && USERID == $this->userid && $this->obj_language != $s_systemLanguage) {
      if ($this->getLanguage() != $this->language) {
	$this->builder->update('users')
	    ->bindString('LanguageInterface', $s_systemLanguage)
	    ->getWhere()
	    ->bindInt('id', $this->userid);
	$this->builder->getResult();
      }
    }
  }

  /**
   * 
   * @param int $i_id
   */
  public function setUserid($i_id)
  {
    $this->userid = $i_id;
  }

  /**
   * @return int
   */
  public function getUserid()
  {
    return $this->userid;
  }

  /**
   * Alias of getUserid
   * @return int
   */
  public function getID()
  {
    return $this->getUserid();
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
   * @param string $s_passwordHash
   */
  public function setPassword($s_passwordHash)
  {
    $this->password = $s_passwordHash;
  }
  
  /**
   * 
   * @return string
   */
  public function getPassword()
  {
    return $this->password;
  }
  
  /**
   * Sets a new password
   *
   * @param string $s_password
   *            plain text password
   * @param boolean $bo_expired
   *            true to set the password to expired
   */
  public function updatePassword($s_password, $bo_expired = false)
  {
    \youconix\core\Memory::type('string', $s_password);

    $this->password = $this->hashing->hash($s_password);
    if ($bo_expired) {
      $this->passwordExpired = 1;
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
   * @param boolean $bo_expired
   */
  public function setPasswordExpired($bo_expired)
  {
    $this->passwordExpired = $bo_expired;
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
   * @param boolean $bo_enabled
   */
  public function setEnabled($bo_enabled)
  {
    $this->enabled = $bo_enabled;
  }

  /**
   *
   * @return boolean
   */
  public function isEnabled()
  {
    return ($this->enabled == 1);
  }

  /**
   * 
   * @param \DateTime $registrated
   */
  public function setRegistrated(\DateTime $registrated)
  {
    $this->registrated = $registrated;
  }

  /**
   *
   * @return \DateTime
   */
  public function getRegistrated()
  {
    return $this->registrated;
  }

  /**
   * 
   * @param \DateTime $loggedIn
   */
  public function setLastLogin(\DateTime $loggedIn)
  {
    $this->lastLogin = $loggedIn;
  }

  /**
   *
   * @return \DateTime
   */
  public function getLastLogin()
  {
    return $this->lastLogin;
  }

  /**
   * Updates the last login date
   */
  public function updateLastLoggedIn()
  {
    $this->loggedIn = new \DateTime();

    $this->builder->update('users')
	->bindInt('lastLogin', $this->loggedIn->getTimestamp())
	->getWhere()
	->bindInt('id', $this->getUserId());
    $this->builder->getResult();
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
   *
   * @return boolean
   */
  public function isBlocked()
  {
    return ($this->blocked == 1);
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
   * @param string $s_profile            
   */
  public function setProfile($s_profile)
  {
    $this->profile = $s_profile;
  }

  /**
   * 
   * @param array $groups
   */
  public function setGroups($groups)
  {
    $this->groups = $groups;
  }

  /**
   * Returns the groups where the user is in
   *
   * @return arrays The groups
   */
  public function getGroups()
  {
    return $this->groups;
  }

  /**
   * Returns the requested user group
   *
   * @return null|\youconix\core\entities\UserGroup
   */
  public function getGroup($i_groupid)
  {
    foreach($this->getGroups() as $group){
      if ($group->getGroup()->getId() == $i_groupid) {
	return $group;
      }
    }
    return null;
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
  
  public function setLanguage($language)
  {
    $this->language = $language;
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
   * 
   * @param boolean $bo_bind
   */
  public function setBindToIp($bo_bind)
  {
    $this->bindToIp = 0;
    if ($bo_bind) {
      $this->bindToIp = 1;
    }
  }

  /**
   * 
   * @return boolean
   */
  public function getBindToIp()
  {
    return ($this->bindToIp == 1);
  }
  
  public function validate(){
    if ($this->loginType == 'normal') {
      $this->a_validation['password'] = 'type:string|required';
    }
    
    return parent::validate();
  }
}
