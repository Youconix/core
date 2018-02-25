<?php

namespace youconix\core\repositories;

use youconix\core\ORM\Entity;

/**
 * @since 1.0
 */
class User extends \youconix\core\ORM\Repository
{

  /**
   *
   * @var \youconix\core\repositories\UserGroup
   */
  protected $userGroups;

  /**
   * PHP5 constructor
   *
   * @param \EntityManager $manager
   * @param \youconix\core\entities\User $userData
   * @param \Builder $builder
   * @param \youconix\core\repositories\UserGroup $userGroups   
   */
  public function __construct(
  \EntityManager $manager, \youconix\core\entities\User $userData, \Builder $builder,
  \youconix\core\repositories\UserGroup $userGroups
  )
  {
    parent::__construct($manager, $userData, $builder);

    $this->userGroups = $userGroups;
  }

  /**
   * Gets the requested users
   *
   * @param array $a_userid
   * @return \youconix\core\entities\User[] array The data objects
   */
  public function getUsersById(array $a_userid)
  {
    \youconix\core\Memory::type('array', $a_userid);

    return $this->findBy(['userid', $a_userid]);
  }

  /**
   * Gets the requested user
   *
   * @param int $i_userid
   *            The user id, leave empty for logged in user
   * @return \youconix\core\models\entities\User
   */
  public function get($i_userid = -1)
  {
    $i_userid = (int) $this->checkUserid($i_userid);

    if ($i_userid == - 1) {
      return $this->getModel([]);
    }

    return $this->find($i_userid);
  }

  /**
   * Returns the user with the given username and email
   *
   * @param string $s_username
   *            The username
   * @param string $s_email
   *            The email address
   * @return \youconix\core\entities\User The user object or null if the user does not exist
   */
  public function getByName($s_username, $s_email = '')
  {
    if (empty($s_email)) {
      return $this->findBy(['nick' => $s_username, 'active' => 1, 'blocked' => 0]);
    } else {
      return $this->findBy(['nick' => $s_username, 'email' => $s_email, 'active' => 1,
	      'blocked' => 0]);
    }
  }

  /**
   * Checks the user id
   *
   * @param int $i_userid
   *            user id, may be -1 for current user
   * @return int
   */
  protected function checkUserid($i_userid)
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
   *            The start position for the search, default 0
   * @return \youconix\core\entities\User[]
   */
  public function getUsers($i_start = 0)
  {
    \youconix\core\Memory::type('int', $i_start);

    return $this->getAll($i_start, 25);
  }

  /**
   * Searches the user(s)
   * Limited on 25 results
   *
   * @param string $s_username
   *            username to search on
   * @return array The users
   */
  public function searchUser($s_username)
  {
    \youconix\core\Memory::type('string', $s_username);

    $this->builder->select('users', '*')
	->order('nick', 'ASC')
	->limit(25)
	->getWhere()
	->bindString('nick', '%' . $s_username . '%', 'OR', 'LIKE')
	->bindString('email', '%' . $s_username . '%', 'OR', 'LIKE');

    $database = $this->builder->getResult();
    $a_result = array(
	'number' => 0,
	'data' => $this->databaseResult2objects($database)
    );
    $a_result['number'] = count($a_result['data']);

    return $a_result;
  }

  /**
   * Returns the user salt
   *
   * @see \youconix\core\models\data\User::getSalt()
   * @param string $s_username
   *            The username
   * @param string $s_loginType
   *            The login type
   * @return NULL|string The salt if the user exists
   */
  public function getSalt($s_username, $s_loginType)
  {
    return $this->userData->getSalt($s_username, $s_loginType);
  }

  /**
   * Activates the user
   *
   * @param string $s_code
   *            The activation code
   * @return boolean True if the user is activated
   * @throws \Exception If activating the user fails
   */
  public function activate($s_code)
  {
    $this->builder->select('users', 'id')
	->getWhere()
	->bindString('activation', $s_code);
    $service_Database = $this->builder->getResult();
    if ($service_Database->num_rows() == 0) {
      return false;
    }

    $i_userid = $service_Database->result(0, 'id');

    try {
      $this->builder->transaction();

      $this->builder->insert('profile')
	  ->bindInt('userid', $i_userid)
	  ->getResult();

      $this->builder->update('users')
	  ->bindString('activation', '')
	  ->bindString('active', '1');

      $this->builder->getWhere()->bindInt('id', $i_userid);
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
   * @param \stdClass $data
   * @return \youconix\core\entities\User The user object
   */
  public function createUser(\stdClass $data = null)
  {
    return $this->getModel($data);
  }

  /**
   * Checks if the username is available
   *
   * @param string $s_username
   *            The username to check
   * @param int $i_userid
   *            The user id who to exclude, -1 for ignore
   * @param String $s_type
   *            login type, default normal
   * @return boolean if the username is available
   */
  public function checkUsername($s_username, $i_userid = -1, $s_type = 'normal')
  {
    \youconix\core\Memory::type('string', $s_username);
    \youconix\core\Memory::type('int', $i_userid);
    \youconix\core\Memory::type('string', $s_type);

    $query = $this->builder->select('users', 'id')
	->getWhere()
	->bindString('nick', $s_username)
	->bindString('loginType', $s_type);

    if ($i_userid != - 1) {
      $query->bindInt('id', $i_userid, 'AND', '<>');
    }

    $service_Database = $this->builder->getResult();
    if ($service_Database->num_rows() != 0) {
      return false;
    }

    return true;
  }

  /**
   * Checks or the given email address is available
   *
   * @param string $s_email
   *            The email address to check
   * @param int $i_userid
   *            The user id who to exclude, -1 for ignore
   * @return boolean True if the email address is available
   */
  public function checkEmail($s_email, $i_userid = -1)
  {
    \youconix\core\Memory::type('string', $s_email);
    \youconix\core\Memory::type('int', $i_userid);

    $this->builder->select('users', 'id')
	->getWhere()
	->bindString('email', $s_email);

    if ($i_userid != - 1) {
      $this->builder->getWhere()->bindInt('id', $i_userid, 'AND', '<>');
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
   * @return array
   */
  public function getSiteAdmins()
  {
    $this->builder->select('users u', 'u.id,u.nick')->innerJoin('group_users g',
								'u.id', 'g.userid');
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
	->bindString('active', ' 1')
	->bindString('blocked', '0');
    $service_Database = $this->builder->getResult();

    $a_users = $service_Database->fetch_assoc();

    return $a_users;
  }

  /**
   *
   * @param array $a_primary
   * @param Entity $entity
   *            Adds the item to the database
   */
  protected function add(array $a_primary, Entity $entity)
  {
    parent::add($a_primary, $entity);

    $this->userGroups->addUserToDefaultGroups($entity);
  }

  /**
   * Deletes the user permantly
   */
  public function delete(Entity $user)
  {
    $this->userGroups->deleteFromUser($user);

    parent::delete($user);
  }
}
