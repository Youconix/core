<?php
namespace youconix\core\models;

/**
 * Model is the general model class.
 * This class is abstract and
 * should be inheritanced by every model.
 * This class handles setting up the database connection
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
 */
abstract class Model extends \youconix\core\Object
{

    /**
     *
     * @var \Validation
     */
    protected $validation;

    /**
     *
     * @var \DAL
     * @deprecated
     *
     */
    protected $service_Database;

    /**
     *
     * @var \Builder
     * @deprecated
     *
     */
    protected $service_QueryBuilder;

    /**
     *
     * @var \DAL
     */
    protected $database;

    /**
     *
     * @var \Builder
     */
    protected $builder;

    

    /**
     * PHP5 constructor
     *
     * @param \Builder $builder            
     * @param \Validation $validation            
     */
    public function __construct(\Builder $builder, \Validation $validation)
    {
        $this->builder = $builder;
        $this->service_QueryBuilder = $this->builder;
        $this->database = $this->builder->getDatabase();
        $this->service_Database = $this->database;
        $this->validation = $validation;
    }

    /**
     * Clones the model
     *
     * @return \youconix\core\models\Model The cloned model
     */
    public function cloneModel()
    {
        return clone $this;
    }
}