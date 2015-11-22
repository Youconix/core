<?php
namespace core\helpers\form;

abstract class FormItem {
    protected $a_errorMessages = array();
    protected $a_values = array();
    protected $bo_required = false;
    protected $s_default = '';
    protected $s_label;
    protected $s_name;
    protected $s_pattern = '';
    protected $s_type = 'text';

    /**
     *
     * @var \core\helpers\html\HTML
     */
    protected $generator;
    
    /**
     *
     * @var \Language
     */
    protected $language;
    
    public function __construct(\core\helpers\HTML $generator, \Language $language)
    {
        $this->generator = $generator;
        $this->language = $language;
    }
    
    public function getDefault(){
        return $this->s_default;
    }
    
    public function getErrorMessages(){
        return $this->a_errorMessages;
    }
    
    public function getLabel(){
        return $this->s_label;
    }
    
    public function getName(){
        return $this->s_name;
    }
    
    public function getPattern(){
        return $this->s_pattern;
    }
    
    public function getType(){
        return $this->s_type;
    }
    
    public function getValues(){
        return $this->a_values;
    }
    
    public function isRequired(){
        return $this->bo_required;
    }
    
    public function setDefault($s_default){
        $this->s_default = $s_default;
    }
    
    public function setErrorMessages($s_message,$s_type = 'default'){
        $this->a_errorMessages[$s_type] = $s_message;
    }
    
    public function setLabel($s_label){
        $this->s_label = $s_label;
    }
    
    public function setName($s_name){
        $this->s_name = $s_name;
    }
    
    public function setRequired(){
        $this->bo_required = true;
    }
    
    public function setStep($i_step){
        $this->i_step = $i_step;
    }
        
    public function setType($s_type){
        $this->s_type = $s_type;
    }
    
    public function setValues($a_values){
        $this->a_values = $a_values;
    }
    
    abstract public function generate($obj_data);
    
    abstract public function getInputCheck();
    
    abstract public function getValidation();
}