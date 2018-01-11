<?php

namespace youconix\core\repositories;

/**
 * Group model.
 * Contains the group data
 *
 * @since 1.0
 *       
 * @see core/services/Session.php
 * @see core/entities/Group.php
 */
class Group extends \youconix\core\ORM\Repository
{
  /**
   *
   * @var \Config
   */
  protected $config;

  /**
   * PHP5 constructor
   *
   * @param \EntityManager $manager
   * @param \Builder $builder
   * @param \youconix\core\entities\Group $group
   */
  public function __construct(\EntityManager $manager, \Builder $builder, \youconix\core\entities\Group $group)
  {
    parent::__construct($manager, $group, $builder);

    /* Load group-names */
    $a_groups = $this->getAll();
    foreach ($a_groups as $group) {
      $s_name = strtoupper($group->getName());
      if (!defined('GROUP_' . $s_name)) {
	define('GROUP_' . $s_name, (int) $group->getId());
      }
    }
  }

  /**
   * 
   * @return array
   */
  public function getDefaultGroups()
  {
    $a_groups = [];
    foreach ($this->a_cache as $group) {
      if ($group->isDefault()) {
	$a_groups[] = $group;
      }
    }

    return $a_groups;
  }

  /**
   * Gets all the registrated groups
   *
   * @return \youconix\core\models\data\Group[]
   */
  public function getGroups()
  {
    return $this->getAll();
  }

  /**
   * Gets the registrated group with the given ID
   *
   * @param int $i_groupid
   *            The group ID
   * @return \youconix\core\models\data\Group The registrated group
   * @throws \TypeException if $i_groupid is not a int
   * @throws \OutOfBoundsException if the group does not exist
   */
  public function getGroup($i_groupid)
  {
    \youconix\core\Memory::type('int', $i_groupid);

    if (!array_key_exists($i_groupid, $this->a_cache)) {
      throw new \OutOfBoundsException("Calling non existing group with id " . $i_groupid);
    }

    return $this->a_cache[$i_groupid];
  }

  /**
   * Generates a new group
   *
   * @return \youconix\core\models\data\Group
   */
  public function generateGroup()
  {
    return $this->getModel();
  }
}
