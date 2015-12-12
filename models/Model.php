<?php
namespace core\models;

/**
 * Miniature-happiness is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Miniature-happiness is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Miniature-happiness. If not, see <http://www.gnu.org/licenses/>.
 *
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
abstract class Model extends \core\Object
{

    /**
     * 
     * @var \core\services\Validation
     */
    protected $validation;

    /**
     * 
     * @var \core\database\DAL
     * @deprecated
     */
    protected $service_Database;
    
    /**
     *
     * @var \Builder
     * @deprecated
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

    protected $a_validation = array();

    protected $bo_throwError = true;

    /**
     * PHP5 constructor
     *
     * @param \Builder $builder
     * @param \core\services\Validation $validation
     */
    public function __construct(\Builder $builder, \core\services\Validation $validation)
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
     * @return Model The cloned model
     */
    public function cloneModel()
    {
        return clone $this;
    }

    /**
     * Validates the model
     *
     * @return Boolean if the model is valid, otherwise false
     */
    public function validate()
    {
        try {
            $this->performValidation();
            return true;
        } catch (\ValidationException $e) {
            return false;
        }
    }

    /**
     * Performs the model validation
     *
     * @throws ValidationException the model is invalid
     */
    protected function performValidation()
    {
        $a_error = array();
        
        $a_keys = array_keys($this->a_validation);
        foreach ($a_keys as $s_key) {
            if (! isset($this->$s_key)) {
                $a_error[] = 'Error validating non existing field ' . $s_key . '.';
                continue;
            }
            
            $this->validation->validateField($s_key,$this->$s_key, $this->a_validation[$s_key]);
        }
        
        $a_error = array_merge($a_error,$this->validation->getErrors());
        
        if (! $this->bo_throwError) {
            return $a_error;
        }
        
        if (count($a_error) > 0) {
            throw new \ValidationException("Error validating : \n" . implode("\n", $a_error));
        }
    }
}