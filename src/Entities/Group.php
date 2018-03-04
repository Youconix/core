<?php

namespace youconix\Core\Entities;

/**
 * Group data model.
 * Contains the group data
 *
 * @Table(name="groups")
 * @ORM\Entity(repositoryClass="youconix\Core\Repositories\Group")
 */
class Group extends \youconix\Core\ORM\AbstractEntity
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
  protected $users;

  /**
   * @param $id
   */
  public function setId($id)
  {
    \youconix\core\Memory::type('int', $id);

    $this->id = $id;
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
   * @param string $name
   */
  public function setName($name)
  {
    \youconix\core\Memory::type('string', $name);

    $this->name = $name;
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
   * @param string $description
   */
  public function setDescription($description)
  {
    \youconix\core\Memory::type('string', $description);

    $this->description = $description;
  }

  /**
   * Returns if the group is default
   *
   * @return boolean if the group is default
   */
  public function isDefault()
  {
    return ($this->default == 1);
  }

  /**
   * Sets the group as default
   *
   * @param boolean $default
   */
  public function setDefault($default)
  {
    \youconix\core\Memory::type('boolean', $default);

    if ($default) {
      $this->default = 1;
    } else {
      $this->default = 0;
    }
  }

  /**
   * Gets the user access level
   *
   * @param int $userid
   *            The user ID
   * @return int The access level defined in /SessionInterface
   */
  public function getLevelByGroupID($userid)
  {
    \youconix\core\Memory::type('int', $userid);

    if (!is_null($this->users)) {
      if (array_key_exists($userid, $this->users)) {
        return $this->users[$userid];
      }
    } else {
      $this->users = [];
    }

    /* Get groupname */
    $this->builder->select('group_users', 'level')
      ->getWhere()
      ->bindInt('groupID', $this->id)
      ->bindInt('userid', $userid);
    $database = $this->builder->getResult();

    if ($database->num_rows() == 0) {
      /* No record found. Access denied */
      $this->users[$userid] = \SessionInterface::ANONYMOUS;
    } else {
      $this->users[$userid] = $database->result(0, 'level');
    }

    return $this->users[$userid];
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

    $result = [];
    if ($database->num_rows() > 0) {
      $result = $database->fetch_assoc();
    }

    return $result;
  }

  /**
   * Adds the group to the database
   *
   * @see \youconix\Core\Models\Equivalent::add()
   */
  protected function add()
  {
    $database = $this->builder->select($this->table, $this->builder->getMaximun('id', 'id'))->getResult();
    $this->id = ($database->result(0, 'id') + 1);

    $this->builder->insert($this->table);
    $this->buildSave();
    $this->builder->getResult();

    if ($this->default != 1) {
      return;
    }

    /* Add users to group */
    $i_groupID = $this->id;
    $this->builder->select('users', 'id,staff');
    $users = $this->builder->getResult()->fetch_assoc();

    foreach ($users as $user) {
      $level = 0;
      if ($user['staff'] == \SessionInterface::ADMIN)
        $level = 2;

      $this->builder->insert('group_users');
      $this->builder->bindInt('groupID', $i_groupID)
        ->bindInt('userid', $user['id'])
        ->bindString('level', $level);
      $this->builder->getResult();
    }
  }

  /**
   * Deletes the group
   * Can not remove a group with members
   *
   * @see \youconix\Core\Models\Equivalent::delete()
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
   * @param int $userid
   * @param int $level
   *            access level, default 0 (user)
   */
  public function addUser($userid, $level = 0)
  {
    \youconix\core\Memory::type('int', $userid);
    \youconix\core\Memory::type('int', $level);

    if ($level < 0 || $level > 2) {
      $level = 0;
    }

    if ($this->getLevelByGroupID($userid) == \SessionInterface::ANONYMOUS) {
      $this->builder->insert("group_users")
        ->bindInt('groupID', $this->id)
        ->bindInt('userid', $userid)
        ->bindString('level', $level)
        ->getResult();
    }
  }

  /**
   * Edits the users access rights for this group
   *
   * @param int $userid
   * @param int $level
   *            access level, default 0 (user)
   */
  public function editUser($userid, $level = 0)
  {
    \youconix\core\Memory::type('int', $userid);
    \youconix\core\Memory::type('int', $level);

    if (!in_array($level, [
      -1,
      0,
      1,
      2
    ]))
      return;

    if ($level == -1) {
      $this->builder->delete("group_users")
        ->getWhere()
        ->bindInt('userid', $userid);
      $this->builder->getResult();
    } else
      if ($this->getLevelByGroupID($userid) == \SessionInterface::ANONYMOUS) {
        $this->builder->insert("group_users")
          ->bindInt('groupID', $this->id)
          ->bindInt('userid', $userid)
          ->bindString('level', $level)
          ->getResult();
      } else {
        $this->builder->update("group_users")
          ->bindString('level', $level)
          ->getWhere()
          ->bindInt('userid', $userid);
        $this->builder->getResult();
      }
  }

  /**
   * Adds all the users to this group if the group is default
   */
  public function addUsersToDefault()
  {
    if ($this->default == 0)
      return;

    $this->builder->select('users', 'userid')->bindString('active', '1')->bindString('blocked', '0');
    $database = $this->builder->getResult();
    $users = $database->fetch_assoc();
    $currentUsers = [];
    $this->builder->select("group_users", "userid")
      ->getWhere()
      ->bindInt('groupID', $this->id);
    $database = $this->builder->getResult();
    if ($database->num_rows() > 0) {
      $currentUsers = $database->fetch_assoc_key('userid');
    }
    $level = \SessionInterface::USER;

    foreach ($users as $user) {
      if (array_key_exists($user, $currentUsers)) {
        continue;
      }

      $this->builder->insert("group_users")
        ->bindInt('groupID', $this->id)
        ->bindInt('userid', $user)
        ->bindString('level', $level)
        ->getResult();
    }
  }

  /**
   * Deletes the user from the group
   *
   * @param int $userid
   */
  public function deleteUser($userid)
  {
    \youconix\core\Memory::type('int', $userid);

    if ($this->getLevelByGroupID($userid) != \SessionInterface::ANONYMOUS) {
      $this->builder->delete('group_users')
        ->getWhere()
        ->bindInt('groupID', $this->id)
        ->bindInt('userid', $userid);
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
    if ($database->num_rows() == 0) {
      return false;
    }

    return true;
  }
}
