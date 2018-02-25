<?php
namespace youconix\core\models;

/**
 * Controller for the page privileges
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 */
class PrivilegeController extends \youconix\core\models\Model
{

    /**
     *
     * @var \youconix\core\services\FileHandler
     */
    protected $file;

    /**
     *
     * @var \Config
     */
    protected $config;

    protected $a_skipDirs = array();

    /**
     * PHP5 constructor
     *
     * @param \Builder $builder
     *            The query builder
     * @param \Validation $validation
     *            The validation service
     * @param \youconix\core\services\FileHandler $file
     *            The file service
     */
    public function __construct(\Builder $builder, \Validation $validation, \youconix\core\services\FileHandler $file, \Config $config)
    {
        parent::__construct($builder, $validation);
        
        $this->file = $file;
        $this->config = $config;
        
        $s_root = $_SERVER['DOCUMENT_ROOT'] . $config->getBase();
        
        $a_skipDirs = [
            '.git',
            'Core',
            'emailImages',
            'emails',
            'files',
            'fonts',
            'includes',
            'install',
            'images',
            'js',
            'language',
            'vendor',
            'openID',
            'stats',
            'tests',
            'admin' . DS . 'data',
            'styles',
            'router.php',
            'routes.php'
        ];
        foreach ($a_skipDirs as $item) {
            $this->a_skipDirs[] = $s_root . $item;
        }
    }

    /**
     * Returns all the controlers
     *
     * @return array The controllers and the site root
     */
    public function getPages()
    {
        $s_root = $_SERVER['DOCUMENT_ROOT'];
        $s_base = $this->config->getBase();
        $s_root .= $s_base;
        
        $a_pages = $this->file->readFilteredDirectory($s_root, $this->a_skipDirs, '\.php');
        
        return [
            $s_root,
            $a_pages
        ];
    }

    /**
     * Returns the rights for the given page
     *
     * @param string $s_page
     *            The page
     * @return array The access rights
     */
    public function getRightsForPage($s_page)
    {
        $a_rights = [
            'page' => $s_page,
            'general' => [
                'id' => - 1,
                'groupID' => - 1,
                'minLevel' => - 2
            ],
            'commands' => []
        ];
        
        /* Check general rights */
        $this->builder->select('group_pages', '*')
            ->order('groupID')
            ->getWhere()
            ->bindString('page', $s_page)
            ->bindString('page', substr($s_page, 1),'OR');

        $database = $this->builder->getResult();
        
        if ($database->num_rows() > 0) {
            $a_data = $database->fetch_assoc();
            $a_rights = array(
                'page' => $s_page,
                'general' => $a_data[0],
                'commands' => array()
            );
        }
        
        $this->builder->select('group_pages_command', '*')
            ->order('groupID')
            ->getWhere()
            ->bindString('page', $s_page);
        
        $database = $this->builder->getResult();
        
        if ($database->num_rows() > 0) {
            $a_rights['commands'] = $database->fetch_assoc();
        }
        
        return $a_rights;
    }

    /**
     * Changes the page rights
     *
     * @param string $s_page
     *            The page
     * @param int $i_rights
     *            The minimun access rights
     * @param int $i_group
     *            The group ID
     */
    public function changePageRights($s_page, $i_rights, $i_group)
    {
        $this->builder->update('group_pages')
            ->bindInt('groupID', $i_group)
            ->bindInt('minLevel', $i_rights)
            ->getWhere()
            ->bindString('page', $s_page)
            ->bindString('page', substr($s_page, 1));
        
        $database = $this->builder->getResult();
        
        if ($database->affected_rows() == 0) {
            $this->addPageRights($s_page, $i_rights, $i_group);
        }
    }

    /**
     * Adds the page rights
     *
     * @param string $s_page
     *            The page
     * @param int $i_rights
     *            The minimun access rights
     * @param int $i_group
     *            The group ID
     */
    public function addPageRights($s_page, $i_rights, $i_group)
    {
        $this->builder->insert('group_pages')
            ->bindInt('groupID', $i_group)
            ->bindInt('minLevel', $i_rights)
            ->bindString('page', $s_page);
        
        $this->builder->getResult();
    }

    /**
     * Removes the page`s rights from the database.
     * In essence, it makes it forget about the page.
     *
     * @param string $s_page
     *            The URL of the particular page to be forgotten about.
     *            
     * @author Roxanna Lugtigheid
     */
    public function deletePageRights($s_page)
    {
        try {
            $this->builder->transaction();
            
            $this->builder->delete('group_pages')
                ->getWhere()
                ->bindString('page', $s_page)
                ->bindString('page', substr($s_page, 1))
                ->getResult();
            
            $this->builder->delete('group_pages_command')
                ->getWhere()
                ->bindString('page', $s_page)
                ->bindString('page', substr($s_page, 1))
                ->getResult();
            
            $this->builder->commit();
        } catch (\DBException $e) {
            $this->builder->rollback();
        }
    }

    /**
     * Adds the view specific rights
     *
     * @param string $s_page
     *            The URL of the particular page
     * @param int $i_group
     *            The group ID
     * @param string $s_command
     *            The view name
     * @param int $i_rights
     *            The minimal access level
     * @return int The new ID, -1 on an error
     */
    public function addViewRight($s_page, $i_group, $s_command, $i_rights)
    {
        try {
            $this->builder->transaction();
            
            $this->builder->select('group_pages_command', 'id')
                ->getWhere()
                ->bindstring('page', $s_page)
                ->bindString('command', $s_command)
                ->bindInt('groupID', $i_group);
            
            $database = $this->builder->getResult();
            if ($database->num_rows() > 0) {
                $i_id = $database->result(0, 'id');
                $this->builder->update('group_pages_command')
                    ->bindInt('minLevel', $i_rights)
                    ->getWhere()
                    ->bindString('page', $s_page)
                    ->bindString('command', $s_command)
                    ->getResult();
            } else {
                $this->builder->insert('group_pages_command')
                    ->bindString('page', $s_page)
                    ->bindString('command', $s_command)
                    ->bindInt('minLevel', $i_rights)
                    ->bindInt($i_group);
                $database = $this->builder->getResult();
                $i_id = $database->getId();
            }
            
            $this->builder->commit();
            
            return $i_id;
        } catch (\DBException $e) {
            $this->builder->rollback();
            return - 1;
        }
    }

    /**
     * Deletes the view specific rights
     *
     * @param int $i_id
     *            The rights ID
     */
    public function deleteViewRight($i_id)
    {
        $this->builder->delete('group_pages_command')
            ->getWhere()
            ->bindInt('id', $i_id)
            ->getResult();
    }
}