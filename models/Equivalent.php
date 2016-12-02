<?php
namespace youconix\core\models;

abstract class Equivalent extends \youconix\core\models\Model
{
    /**
     * 
     * @var \youconix\core\models\EquavalentHelper
     */
    protected $helper;
    
    protected $s_table = '';

    protected $s_primary = 'id';

    protected $a_modelFields = array();

    /**
     * PHP5 constructor
     *
     * @param \Builder $builder            
     * @param \Validation $validation            
     * @param \youconix\core\models\EquavalentHelper $helper
     */
    public function __construct(\Builder $builder, \Validation $validation,\youconix\core\models\EquavalentHelper $helper)
    {
        parent::__construct($builder, $validation);
        
        $this->helper = $helper;
        
        $this->detectTable();
        $this->detectFields();
        $this->detectValidation();
    }

    /**
     * Automatically detects the table
     */
    protected function detectTable()
    {
        if (empty($this->s_table)) {
            $s_className = get_class($this);
            $s_className = explode('\\', $s_className);
            $s_className = end($s_className);
            $s_className = lcfirst($s_className);
            
            $s_className = preg_replace('/([A-Z])/', '_$1', $s_className);
            $s_className = strtolower($s_className);
            $this->s_table = $s_className;
        }
    }

    /**
     * Automatically detects all the fields in the table
     */
    protected function detectFields()
    {
        $this->a_modelFields = $this->helper->get($this->s_table);
    }
    
    /**
     * Automatically generates the validation rules if they are not defined yet
     */
    protected function detectValidation(){
        if( count($this->a_validation) > 0 ){
            return;
        }
        
        foreach($this->a_modelFields AS $s_field => $a_settings){
            if( $a_settings['primary'] ){  
                continue;
            }
            switch($a_settings['type']){
                case 'varchar':
                case 'text' :
                case 'enum' :
                    $s_validation = 'type:string';
                break;
                case 'int':
                case 'smallint':
                case 'mediumint':
                case 'longint':
                    $s_validation = 'type:int';
                    break;
                case 'double':
                case 'float' :
                    $s_validation = 'type:float';
                    break;
            }
            if( (!$a_settings['null']) && (empty($a_settings['default'])) ){
                $s_validation .= '|required';
            }
            if( $a_settings['type'] == 'enum' ){
                $s_validation .= '|set:'.implode(',',$a_settings['set']);
            }
            $this->a_validation[$s_field] = $s_validation;
        }
    }
    
    /**
     * Returns the table
     * 
     * @return string
     */
    public function getTable(){
        return $this->s_table;
    }
    
    /**
     * Returns the primary key
     * 
     * @return string
     */
    public function getPrimary(){
        return $this->s_primary;
    }

    /**
     * Sets the user data
     *
     * @param \stdClass $data
     *            user data
     */
    public function setData(\stdClass $data)
    {
        $keys = get_object_vars($data);

        foreach($keys AS $s_field => $value){
            if( is_numeric($s_field) ){
              continue;
            }

            $this->$s_field = $value;
        }
    }

    /**
     * Returns the item with the given id
     * 
     * @param int $i_id     The id
     * @throws \RuntimeException    If the item does not exists
     * @return Equivalent   The item
     */
    public function get($i_id)
    {
        $s_primary = $this->s_primary;
        
        $this->builder->select($this->s_table, '*')->bindInt($s_primary, $i_id);
        
        $database = $this->builder->getResult();
        if ($database->num_rows() == 0) {
            throw new \RuntimeException('Call to unknown ' . $this->s_primary . ' ' . $i_id . ' from table ' . $this->s_table . '.');
        }
        $a_data = $database->fetch_object();
        
        $item = clone $this;
        $item->setData($a_data[0]);
        return $item;
    }
    
    /**
     * Finds the items with the given keys and values
     * 
     * @param array $relations  The key and values as associate array
     * @return \youconix\core\models\Equivalent[]
     */
    public function find($relations){
        $where = $this->builder->select($this->table,'*')->getWhere();
        foreach($relations AS $s_key => $s_value){
            $where->bindString($s_key, $s_value);
        }
        $database = $this->builder->getResult();
        $result = [];
        if( $database->num_rows() > 0 ){
            foreach($database->fetch_object() AS $item){
                $object = clone $this;
                $object->setdata($item);
                $result[] = $object;
            }
        }
        return $result;
    }

    /**
     * Saves the item
     */
    public function save()
    {
        $s_primary = $this->s_primary;
        
        if (is_null($this->$s_primary)) {
            $this->add();
        } else {
            $this->update();
        }
    }

    /**
     * Adds the item to the database
     */
    protected function add()
    {
        $s_primary = $this->s_primary;
        
        $this->builder->insert($this->s_table);
        $this->buildSave();
        $database = $this->builder->getResult();
        
        $this->$s_primary = $database->getId();
    }

    /**
     * Updates the item in the database
     */
    protected function update()
    {
        $s_primary = $this->s_primary;
        
        $this->builder->update($this->s_table);
        $this->buildSave();
        $this->builder->getWhere()->bindInt($this->s_primary,$this->$s_primary);
        $this->builder->getResult();
    }

    /**
     * Builds the query
     */
    protected function buildSave()
    {
        foreach ($this->a_modelFields as $s_field => $a_settings) {
            switch ($a_settings['type']) {
                case 'int':
                case 'smallint':
                case 'mediumint':
                case 'longint':
                    $this->builder->bindInt($s_field, $this->$s_field);
                    break;
                case 'double':
                case 'float' :
                    $this->builder->bindFloat($s_field, $this->$s_field);
                    break;
                case 'blob':
                    $this->builder->bindBlob($s_field, $this->$s_field);
                    break;
                default:
                    $this->builder->bindString($s_field, $this->$s_field);
                    break;
            }
        }
    }

    /**
     * Deletes the item from the database
     */
    public function delete()
    {
        $s_primary = $this->s_primary;
        
        $this->builder->delete($this->s_table);
        $this->builder->getWhere()->bindInt($s_primary, $this->$s_primary);
        $this->builder->getResult();
    }
    
    /**
     * Returns the objects pointing to this one
     * 
     * @param string $s_class   The class name
     * @param string $s_foreignKey  The foreign key, default [table]_id
     * @param string $s_localKey    The local key, default the primary key
     * @throws \LogicException
     * @return \youconix\core\models\Equivalent[]
     */
    protected function has($s_class,$s_foreignKey = '',$s_localKey = ''){
        $class = \Loader::inject($s_class);
        if( !($class instanceof \youconix\core\models\Equivalent) ){
            throw new \LogicException('Class '.$s_class.' is not an Equivalent and can not be called though Equivalent::has().');
        }
        $s_table = $class->getTable();
        
        if( empty($s_foreignKey) ){
            $s_foreignKey = $this->s_table.'_id';
        }
        
        // Get data
        if( empty($s_localKey) ){
            $s_localKey = $s_table.'_'.$class->getPrimary();
        }
        $local = $this->$s_localKey;
        $this->builder->select($s_table, '*')->getWhere()->bindInt($s_foreignKey, $local);
        $database = $this->builder->getResult();
        
        if( $database->num_rows() == 0 ){
            return [];
        }
        $dataRaw = $database->fetch_object();
        $data = [];
        foreach($dataRaw AS $item){
            $newClass = clone $class;
            $newClass->setdata($item);
            $data[] = $newClass;
        }
        return $data;
    }
    
    /**
     * Returns the objects this class has a reference to
     *
     * @param string $s_class   The class name
     * @param string $s_foreignKey  The foreign key, default id
     * @param string $s_localKey    The local key, default [table]_id
     * @throws \LogicException
     * @return \youconix\core\models\Equivalent[]
     */
    protected function belongsTo($s_class,$s_foreignKey = '',$s_localKey = ''){
        $class = \Loader::inject($s_class);
        if( !($class instanceof \youconix\core\models\Equivalent) ){
            throw new \LogicException('Class '.$s_class.' is not an Equivalent and can not be called though Equivalent::has().');
        }
        $s_table = $class->getTable();
        
        if( empty($s_foreignKey) ){
            $s_foreignKey = $s_table.'_id';
        }
        
        // Get data
        if( empty($s_localKey) ){
            $s_localKey = $this->getPrimary();
        }
                
        $local = $this->$s_localKey;
        $this->builder->select($this->s_table,'*')->getWhere()->bindInt($s_foreignKey, $local);
        $database = $this->builder->getResult();
        
        if( $database->num_rows() == 0 ){
            return [];
        }
        $dataRaw = $database->fetch_object();
        $data = [];
        foreach($dataRaw AS $item){
            $newClass = clone $class;
            $newClass->setdata($item);
            $data[] = $newClass;
        }
        return $data;
    }

    public function __get($s_key){
        $s_call = 'get'.ucfirst($s_key);
        if(method_exists($this,$s_call) ){
          return $this->$s_call();
        }

        if( exist($this->$s_key) ){
          return $this->$s_key;
        }
        return null;
    }

    public function __set($s_key,$s_value){
        $s_call = 'set'.ucfirst($s_key);
        if(method_exists($this,$s_call) ){
          return $this->$s_call($s_value);
        }

        if( exist($this->$s_key) ){
          $this->$s_key = $s_value;
        }
    }
}