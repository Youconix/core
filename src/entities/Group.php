<?php

namespace youconix\core\entities;

/**
 * Group data model.
 * Contains the group data
 * 
 * @Table(name="groups")
 * @ORM\Entity(repositoryClass="youconix\Core\repositories\Group")
 */
class Group extends \youconix\core\ORM\Entity
{

  /**
   *
   * @Id
   * @GeneratedValue
   * @Column(type="integer")
   */
  protected $id;

  /**
   *
   * @Column(type="string")
   */
  protected $name;

  /**
   *
   * @Column(type="boolean", name="automatic")
   */
  protected $default = 0;

  /**
   *
   * @Column(type="string")
   */
  protected $description;

  /**
   *
   * @var array
   */
  protected $a_users;

  public function setId($i_id)
  {
    \youconix\core\Memory::type('int', $i_id);
    
    $this->id = $i_id;
  }
  
  /**
   *
   * @return int ID
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   *
   * @return string name
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   *
   * @param string $s_name            
   */
  public function setName($s_name)
  {
    \youconix\core\Memory::type('string', $s_name);

    $this->name = $s_name;
  }

  /**
   *
   * @return string description
   */
  public function getDescription()
  {
    return $this->description;
  }

  /**
   *
   * @param string $s_description            
   */
  public function setDescription($s_description)
  {
    \youconix\core\Memory::type('string', $s_description);

    $this->description = $s_description;
  }

  /**
   * Returns if the group is default
   *
   * @return boolean if the group is default
   */
  public function isDefault()
  {
    return ($this->automatic == 1);
  }

  /**
   * Sets the group as default
   *
   * @param boolean $bo_default
   *            to true to make the group default
   */
  public function setDefault($bo_default)
  {
    \youconix\core\Memory::type('boolean', $bo_default);

    if ($bo_default) {
      $this->automatic = 1;
    } else {
      $this->automatic = 0;
    }
  }

  /**
   * Gets the user access level
   *
   * @param int $i_userid
   *            The user ID
   * @return int The access level defined in /include/services/Session.inc.php
   */
  public function getLevelByGroupID($i_userid)
  {
    \youconix\core\Memory::type('int', $i_userid);

    if (!is_null($this->a_users)) {
      if (array_key_exists($i_userid, $this->a_users)) {
	return $this->a_users[$i_userid];
      }
    } else {
      $this->a_users = array();
    }

    /* Get groupname */
    $this->builder->select('group_users', 'level')
	->getWhere()
	->bindInt('groupID', $this->id)
	->bindInt('userid', $i_userid);
    $database = $this->builder->getResult();

    if ($database->num_rows() == 0) {
      /* No record found. Access denied */
      $this->a_users[$i_userid] = \Session::ANONYMOUS;
    } else {
      $this->a_users[$i_userid] = $database->result(0, 'level');
    }

    return $this->a_users[$i_userid];
  }

  /**
   * Gets all the members from the group
   *
   * @return array The members from the group
   */
  public function getMembersByGroup()
  {
    $this->builder->select('group_users g', 'g.level,u.nick AS username,u.id')
	->innerJoin('users u', 'g.userid', 'u.id')
	->order('u.nick', 'ASC');
    $this->builder->getWhere()->bindInt('g.groupID', $this->id);
    $database = $this->builder->getResult();

    $a_result = array();
    if ($database->num_rows() > 0) {
      $a_result = $database->fetch_assoc();
    }

    return $a_result;
  }

  /**
   * Adds the group to the database
   * 
   * @see \youconix\core\models\Equivalent::add()
   */
  protected function add()
  {
    $database = $this->builder->select($this->s_table, $this->builder->getMaximun('id', 'id'))->getResult();
    $this->id = ($database->result(0, 'id') + 1);

    $this->builder->insert($this->s_table);
    $this->buildSave();
    $this->builder->getResult();

    if ($this->automatic != 1) {
      return;
    }

    /* Add users to group */
    $i_groupID = $this->id;
    $this->builder->select('users', 'id,staff');
    $a_users = $this->builder->getResult()->fetch_assoc();

    foreach ($a_users as $a_user) {
      $i_level = 0;
      if ($a_user['staff'] == \Session::ADMIN)
	$i_level = 2;

      $this->builder->insert('group_users');
      $this->builder->bindInt('groupID', $i_groupID)
	  ->bindInt('userid', $a_user['id'])
	  ->bindString('level', $i_level);
      $this->builder->getResult();
    }
  }

  /**
   * Deletes the group
   * Can not remove a group with members
   * 
   * @see \youconix\core\models\Equivalent::delete()
   */
  public function delete()
  {
    /* Check if group is in use */
    if ($this->inUse()) {
      return;
    }

    parent::delete();
  }

  /**
   * Adds a user to the group
   *
   * @param int $i_userid
   *            userid
   * @param int $i_level
   *            access level, default 0 (user)
   */
  public function addUser($i_userid, $i_level = 0)
  {
    \youconix\core\Memory::type('int', $i_userid);
    \youconix\core\Memory::type('int', $i_level);

    if ($i_level < 0 || $i_level > 2)
      $i_level = 0;

    if ($this->getLevelByGroupID($i_userid) == \Session::ANONYMOUS) {
      $this->builder->insert("group_users")
	  ->bindInt('groupID', $this->id)
	  ->bindInt('userid', $i_userid)
	  ->bindString('level', $i_level)
	  ->getResult();
    }
  }

  /**
   * Edits the users access rights for this group
   *
   * @param int $i_userid
   *            userid
   * @param int $i_level
   *            access level, default 0 (user)
   */
  public function editUser($i_userid, $i_level = 0)
  {
    \youconix\core\Memory::type('int', $i_userid);
    \youconix\core\Memory::type('int', $i_level);

    if (!in_array($i_level, array(
	    - 1,
	    0,
	    1,
	    2
	)))
      return;

    if ($i_level == - 1) {
      $this->builder->delete("group_users")
	  ->getWhere()
	  ->bindInt('userid', $i_userid);
      $this->builder->getResult();
    } else
    if ($this->getLevelByGroupID($i_userid) == \Session::ANONYMOUS) {
      $this->builder->insert("group_users")
	  ->bindInt('groupID', $this->id)
	  ->bindInt('userid', $i_userid)
	  ->bindString('level', $i_level)
	  ->getResult();
    } else {
      $this->builder->update("group_users")
	  ->bindString('level', $i_level)
	  ->getWhere()
	  ->bindInt('userid', $i_userid);
      $this->builder->getResult();
    }
  }

  /**
   * Adds all the users to this group if the group is default
   */
  public function addUsersToDefault()
  {
    if ($this->i_default == 0)
      return;

    $this->builder->select('users', 'userid')->bindString('active', '1')->bindString('blocked', '0');
    $database = $this->builder->getResult();
    $a_users = $database->fetch_assoc();
    $a_currentUsers = array();
    $this->builder->select("group_users", "userid")
	->getWhere()
	->bindInt('groupID', $this->id);
    $database = $this->builder->getResult();
    if ($database->num_rows() > 0) {
      $a_currentUsers = $database->fetch_assoc_key('userid');
    }
    $i_level = \Session::USER;

    foreach ($a_users as $i_user) {
      if (array_key_exists($i_user, $a_currentUsers))
	continue;

      $this->builder->insert("group_users")
	  ->bindInt('groupID', $this->id)
	  ->bindInt('userid', $i_user)
	  ->bindString('level', $i_level)
	  ->getResult();
    }
  }

  /**
   * Deletes the user from the group
   *
   * @param int $i_userid
   */
  public function deleteUser($i_userid)
  {
    \youconix\core\Memory::type('int', $i_userid);

    if ($this->getLevelByGroupID($i_userid) != \Session::ANONYMOUS) {
      $this->builder->delete('group_users')
	  ->getWhere()
	  ->bindInt('groupID', $this->id)
	  ->bindInt('userid', $i_userid);
      $this->builder->getResult();
    }
  }

  /**
   * Checks if the group is in use
   *
   * @return boolean if the group is in use
   */
  public function inUse()
  {
    if (is_null($this->id))
      return false;

    $this->builder->select('group_users', 'id')
	->getWhere()
	->bindInt('groupID', $this->id);
    $database = $this->builder->getResult();
    if ($database->num_rows() == 0)
      return false;

    return true;
  }
}
