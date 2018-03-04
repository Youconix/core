<?php

namespace youconix\Core\Entities;

/**
 * @Table(name="group_users")
 */
class UserGroup extends \youconix\Core\ORM\AbstractEntity
{

  /**
   *
   * @OneToOne(targetEntity="User")
   * @JoinColumn(name="userid", referencedColumnName="userid")
   * @var \youconix\Core\Entities\User
   */
  protected $user;

  /**
   *
   * @OneToOne(targetEntity="Group")
   * @JoinColumn(name="groupID", referencedColumnName="id")
   * @var \youconix\Core\Entities\Group
   */
  protected $group;

  /**
   *
   * @Id
   * @GeneratedValue
   * @Column(type="integer")
   */
  protected $id = null;

  /**
   *
   * @Column(type="integer")
   */
  protected $level;

  /**
   * Returns the message ID
   *
   * @return int
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   *
   * @param int $id
   */
  public function setId($id)
  {
    $this->id = $id;
  }

  /**
   *
   * @param int $level
   */
  public function setLevel($level)
  {
    $this->level = $level;
  }

  /**
   *
   * @return int
   */
  public function getLevel()
  {
    return $this->level;
  }

  /**
   * @return User
   */
  public function getUser()
  {
    return $this->user;
  }

  /**
   * @param $user
   */
  public function setUser($user)
  {
    $this->user = $user;
  }

  /**
   * @param $id
   */
  public function setGroupID($id)
  {
    $this->groupID = $id;
  }

  /**
   * @param Group $group
   */
  public function setGroup(Group $group)
  {
    $this->group = $group;
  }

  /**
   * @return Group
   */
  public function getGroup()
  {
    return $this->group;
  }

  /**
   * Returns the colour corresponding the users level
   *
   * @return string The colour
   */
  public function getColor()
  {
    switch ($this->getLevel()) {
      case \SessionInterface::ANONYMOUS:
        return \SessionInterface::ANONYMOUS_COLOR;

      case \SessionInterface::USER:
        return \SessionInterface::USER_COLOR;

      case \SessionInterface::MODERATOR:
        return \SessionInterface::MODERATOR_COLOR;

      case \SessionInterface::ADMIN:
        return \SessionInterface::ADMIN_COLOR;
    }
  }

  /**
   * Checks is the user has moderator rights
   *
   * @return boolean True if the visitor has moderator rights, otherwise false
   */
  public function isUser()
  {
    return ($this->getLevel() >= \SessionInterface::USER);
  }

  /**
   * Checks is the user has moderator rights
   *
   * @return boolean True if the visitor has moderator rights, otherwise false
   */
  public function isModerator()
  {
    return ($this->getLevel() >= \SessionInterface::MODERATOR);
  }

  /**
   * Checks is the user has administrator rights
   *
   * @return boolean True if the visitor has administrator rights, otherwise false
   */
  public function isAdmin()
  {
    return ($this->getLevel() >= \SessionInterface::ADMIN);
  }
}
