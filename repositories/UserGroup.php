<?php

namespace youconix\core\repositories;

use youconix\core\entities\User;
use youconix\core\entities\Group;

/**
 * @since 2.0
 */
class UserGroup extends \youconix\core\ORM\Repository
{

  /**
   * @var \youconix\core\repositories\Group
   */
  protected $groups;

  /**
   * PHP5 constructor
   *
   * @param \EntityManager $manager
   * @param \youconix\core\entities\UserGroup $userGroup
   * @param \Builder $builder
   * @param \youconix\core\repositories\Group $groups
   */
  public function __construct(
    \EntityManager $manager, 
      \youconix\core\entities\UserGroup $userGroup, 
      \Builder $builder, 
      \youconix\core\repositories\Group $groups
  ){
    parent::__construct($manager, $userGroup, $builder);
    
    $this->groups = $groups;
  }

  protected function create(User $user, Group $group)
  {
    $userGroup = $this->getModel();
    $userGroup->setUser($user);
    $userGroup->setGroup($group);
    $userGroup->setLevel(1);

    $this->save($userGroup);
  }

  /**
   * 
   * @param User $user
   */
  public function addUserToDefaultGroups(User $user)
  {
    $a_userGroups = [];
    foreach($this->groups->getDefaultGroups() as $group){
      $a_userGroups[] = $this->create($user, $group);
    }
    $user->setGroups($a_userGroups);
  }

  /**
   * 
   * @param User $user
   */
  public function deleteFromUser(User $user)
  {
    $this->builder->delete($this->model->getTableName())
	->getWhere()
	->bindInt('user_id', $user->getUserId(), 'AND', 'IN')
	->getResult();
  }
}
