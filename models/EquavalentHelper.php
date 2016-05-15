<?php
namespace youconix\core\models ;

class EquavalentHelper {
    /**
     * 
     * @var \Builder
     */
    private $builder;
    private $a_descriptions = array();
    
    public function __construct(\Builder $builder){
        $this->builder = $builder;
    }
    
    /**
     * Returns if the object schould be treated as singleton
     *
     * @return boolean True if the object is a singleton
     */
    public static function isSingleton()
    {
        return true;
    }   
    
    public function get($s_table){
        if( !array_key_exists($s_table, $this->a_descriptions) ){
            $this->a_descriptions[$s_table] = $this->builder->decribeFields($s_table);
        }
        
        return $this->a_descriptions[$s_table];
    }
}