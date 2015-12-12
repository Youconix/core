<?php
namespace youconix\core\models;

/**
 * Group model.
 * Contains the group data
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
 *       
 * @see core/services/Session.inc.php
 * @see core/models/data/Data_Group.inc.php
 */
class Groups extends Model
{

    /**
     *
     * @var \youconix\core\models\data\Group
     */
    protected $group;

    /**
     *
     * @var \Config
     */
    protected $config;

    protected $a_groups;

    /**
     * PHP5 constructor
     *
     * @param \Builder $builder            
     * @param \Validation $validation            
     * @param \youconix\core\models\data\Group $group            
     * @param \Config $config            
     */
    public function __construct(\Builder $builder, \Validation $validation, \youconix\core\models\data\Group $group, \Config $config)
    {
        parent::__construct($builder, $validation);
        
        $this->group = $group;
        $this->config = $config;
        
        $this->a_groups = array();
        
        /* Load group-names */
        $this->builder->select('groups', '*')->order('id');
        $service_Database = $this->builder->getResult();
        
        $a_groups = $service_Database->fetch_assoc();
        foreach ($a_groups as $a_group) {
            $model = $this->group->cloneModel();
            $model->setData($a_group);
            $this->a_groups[$a_group['id']] = $model;
            
            $s_name = strtoupper($a_group['name']);
            if (! defined('GROUP_' . $s_name)) {
                define('GROUP_' . $s_name, (int) $a_group['id']);
            }
        }
    }

    /**
     * Gets all the registrated groups
     *
     * @return \youconix\core\models\data\Group[]
     */
    public function getGroups()
    {
        return $this->a_groups;
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
        
        if (! array_key_exists($i_groupid, $this->a_groups)) {
            throw new \OutOfBoundsException("Calling non existing group with id " . $i_groupid);
        }
        
        return $this->a_groups[$i_groupid];
    }

    /**
     * Gets the user access level for current group
     * Based on the controller
     *
     * @param int $i_userid
     *            The user ID
     * @return int The access level defined in /include/services/Session.inc.php
     */
    public function getLevel($i_userid, $i_groupid = -1)
    {
        \youconix\core\Memory::type('int', $i_userid);
        \youconix\core\Memory::type('int', $i_groupid);
        
        if ($i_groupid == - 1) {
            $s_page = $this->config->getPage();
            $this->builder->select('group_pages', 'groupID')
                ->getWhere()
                ->addAnd('page', 's', $s_page);
            $service_Database = $this->builder->getResult();
            
            if ($service_Database->num_rows() == 0) {
                return \Session::ANONYMOUS;
            }
            
            $i_groupid = (int) $service_Database->result(0, 'groupID');
        }
        
        return $this->getLevelByGroupID($i_groupid, $i_userid);
    }

    /**
     * Gets the user access level for the given group
     *
     * @param int $i_groupid
     *            The group ID
     * @param int $i_userid
     *            The user ID
     * @return int The access level defined in /include/services/Session.inc.php
     */
    public function getLevelByGroupID($i_groupid, $i_userid)
    {
        \youconix\core\Memory::type('int', $i_groupid);
        \youconix\core\Memory::type('int', $i_userid);
        
        $this->builder->select('group_users', 'level')
            ->getWhere()
            ->addAnd(array(
            'userid',
            'groupID'
        ), array(
            'i',
            'i'
        ), array(
            $i_userid,
            $i_groupid
        ));
        $service_Database = $this->builder->getResult();
        
        if ($service_Database->num_rows() > 0) {
            return $service_Database->result(0, 'level');
        }
        
        return \Session::ANONYMOUS;
    }

    /**
     * Generates a new group
     *
     * @return \youconix\core\models\data\Group
     */
    public function generateGroup()
    {
        return $this->group->cloneModel();
    }

    /**
     * Gets the groups with level from the given user
     *
     * @param int $i_userid
     *            The userid
     * @return array The users groups with level
     */
    public function getGroupsLevel($i_userid)
    {
        \youconix\core\Memory::type('int', $i_userid);
        
        $a_groups = array();
        foreach ($this->a_groups as $obj_group) {
            $a_groups[$obj_group->getID()] = $obj_group->getLevelByGroupID($i_userid);
        }
        
        return $a_groups;
    }

    /**
     * Adds a user to the default groups
     *
     * @param int $i_userid
     *            The userid
     * @param int $i_level
     *            The requested level (0|1|2)
     */
    public function addUserDefaultGroups($i_userid, $i_level = 0)
    {
        \youconix\core\Memory::type('int', $i_userid);
        \youconix\core\Memory::type('int', $i_level);
        
        foreach ($this->a_groups as $obj_group) {
            if (! $obj_group->isDefault()) {
                continue;
            }
            
            $obj_group->addUser($i_userid, $i_level);
        }
    }

    /**
     * Deletes a user from all the groups
     *
     * @param int $i_userid
     *            The userid
     */
    public function deleteUserFromGroups($i_userid)
    {
        \youconix\core\Memory::type('int', $i_userid);
        
        foreach ($this->a_groups as $obj_group) {
            $obj_group->deleteUser($i_userid);
        }
    }

    /**
     * Edits the access levels for the given groups
     *
     * @param int $i_userid            
     * @param array $a_groups            
     * @param int $i_level
     *            level
     * @throws \OutOfBoundsException the group ID does niet exist
     */
    public function editUserLevel($i_userid, $a_groups, $i_level)
    {
        \youconix\core\Memory::type('int', $i_userid);
        \youconix\core\Memory::type('array', $a_groups);
        \youconix\core\Memory::type('int', $i_level);
        
        foreach ($a_groups as $i_group) {
            if (! is_int($i_group) || ! array_key_exists($i_group, $this->a_groups)) {
                throw new \OutOfBoundsException("Unknown group id " . $i_group . '.');
            }
            
            $this->a_groups[$i_group]->editUser($i_userid, $i_level);
        }
    }
}