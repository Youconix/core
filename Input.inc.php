<?php
namespace core;

class Input implements \Input,\ArrayAccess {
    /**
     * @var \Security
     */
    protected $security;
    
    /**
     * @var \Validation
     */
    protected $validation;
    
    protected $s_type;
    
    protected $a_container = array();
    
    protected $a_previous = array();
    
    public function __construct(\Security $security,\Validation $validation){
        $this->security = $security;
        $this->validation = $validation;
    }
 
    /**
     * Secures the input from the given type
     * 
     * @param string $s_type	The global variable type (POST | GET | REQUEST | SESSION | SERVER ) 
     * @param array $a_fields	The input type rules
     */
    public function parse($s_type,$a_fields){
        $this->s_type = $s_type;
        
        $this->a_container = $this->security->secureInput($s_type, $a_fields);
    }
    
    /**
     * Checks if the input has the given field
     * 
     * @param string $s_key	The field name
     * @return boolean
     */
    public function has($s_key){
        return array_key_exists($s_key,$this->a_container);
    }
    
    /**
     * Returns the value from the given field
     * Gives the default value if the field does not exist
     * 
     * @param string $s_key	The field name
     * @param string $s_default	The default value
     * @return The value
     */
    public function getDefault($s_key,$s_default = ''){
        if( !$this->has($s_key) ){
            return $s_default;
        }
        
        return $this->a_container[$s_key];
    }
    
    /**
     * Returns the value from the given field
     *
     * @param string $s_key	The field name
     * @return The value
     * @throws \OutOfBoundsException If the field does not exist
     */
    public function get($s_key){
        if( !$this->has($s_key) ){
            throw new \OutOfBoundsException('Key '.$s_key.' is not present in collection.');
        }
        
        return $this->a_container[$s_key];
    }
    
    /**
     * Validates the input
     * 
     * @param array $a_rules	The validation rules
     * @return boolean	True if the input is valid
     */
    public function validate($a_rules){
        $a_errors = $this->validateErrors($a_rules);
        
        return (count($a_errors) == 0 );
    }
    
    /**
     * Validates the input and returns the validation errors
     * 
     * @param array $a_rules	The validation rules
     * @return array	The errors, empty array if the input is valid
     */
    public function validateErrors($a_rules){
        if( $this->validation->validate($a_rules, $this->a_container) ){
            return array();
        }
        
        return $this->validation->getErrors();
    }
    
    /**
     * Returns the validation service
     * 
     * @return \Validation
     */
    public function getValidation(){
        return $this->validation;
    }
    
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }
    
    public function offsetExists($offset) {
        return $this->has($offset);
    }
    
    public function offsetUnset($offset) {
        unset($this->container[$offset]);
    }
    
    public function offsetGet($offset) {
        return $this->get($offset);
    }
    
    /**
     * Returns the current values as array
     * 
     * @return array
     */
    public function toArray(){
        return $this->a_container;
    }
    
    /**
     * Sets the previous request values
     * 
     * @param array $a_data The values
     */
    public function setPrevious($a_data){
        $this->a_previous = $a_data;    
    }
    
    /**
     * Returns the previous request values
     *
     * @return array The values
     */
    public function getPrevious(){
        return $this->a_previous;
    }
}