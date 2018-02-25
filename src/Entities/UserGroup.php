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
   * @param int $i_id
   */
  public function setId($i_id)
  {
    $this->id = $i_id;
  }

  /**
   * 
   * @param int $i_level
   */
  public function setLevel($i_level)
  {
    $this->level = $i_level;
  }

  /**
   * 
   * @return int
   */
  public function getLevel()
  {
    return $this->level;
  }

  public function getUser()
  {
    return $this->user;
  }

  public function setUser($user)
  {
    $this->user = $user;
  }
  
  public function setGroupID($id){
    $this->groupID = $id;
  }

  public function setGroup($group)
  {
    $this->group = $group;
  }

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
   * Checks is the user has moderator rights
   *
   * @return boolean True if the visitor has moderator rights, otherwise false
   */
  public function isUser()
  {
    return ($this->getLevel() >= \Session::USER);
  }

  /**
   * Checks is the user has moderator rights
   *
   * @return boolean True if the visitor has moderator rights, otherwise false
   */
  public function isModerator()
  {
    return ($this->getLevel() >= \Session::MODERATOR);
  }

  /**
   * Checks is the user has administrator rights
   *
   * @return boolean True if the visitor has administrator rights, otherwise false
   */
  public function isAdmin()
  {
    return ($this->getLevel() >= \Session::ADMIN);
  }
}
