<?php
namespace youconix\core\helpers\form;

/**
 * Form item parent class
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 2.0
 *       
 */
abstract class FormItem
{

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
     * @var \youconix\core\helpers\html\HTML
     */
    protected $generator;

    /**
     *
     * @var \Language
     */
    protected $language;

    public function __construct(\youconix\core\helpers\HTML $generator, \Language $language)
    {
        $this->generator = $generator;
        $this->language = $language;
    }

    /**
     *
     * @return string
     */
    public function getDefault()
    {
        return $this->s_default;
    }

    /**
     *
     * @return array
     */
    public function getErrorMessages()
    {
        return $this->a_errorMessages;
    }

    /**
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->s_label;
    }

    /**
     *
     * @return string
     */
    public function getName()
    {
        return $this->s_name;
    }

    /**
     *
     * @return string
     */
    public function getPattern()
    {
        return $this->s_pattern;
    }

    /**
     *
     * @return string
     */
    public function getType()
    {
        return $this->s_type;
    }

    /**
     *
     * @return array
     */
    public function getValues()
    {
        return $this->a_values;
    }

    /**
     *
     * @return boolean
     */
    public function isRequired()
    {
        return $this->bo_required;
    }

    /**
     *
     * @param string $s_default            
     */
    public function setDefault($s_default)
    {
        $this->s_default = $s_default;
    }

    /**
     *
     * @param string $s_message            
     * @param string $s_type            
     */
    public function setErrorMessages($s_message, $s_type = 'default')
    {
        $this->a_errorMessages[$s_type] = $s_message;
    }

    /**
     *
     * @param string $s_label            
     */
    public function setLabel($s_label)
    {
        $this->s_label = $s_label;
    }

    /**
     *
     * @param string $s_name            
     */
    public function setName($s_name)
    {
        $this->s_name = $s_name;
    }

    public function setRequired()
    {
        $this->bo_required = true;
    }

    /**
     *
     * @param string $s_type            
     */
    public function setType($s_type)
    {
        $this->s_type = $s_type;
    }

    /**
     *
     * @param array $a_values            
     */
    public function setValues($a_values)
    {
        $this->a_values = $a_values;
    }

    /**
     *
     * @param \youconix\core\models\Model $obj_data            
     * @return string
     */
    abstract public function generate($obj_data);

    /**
     *
     * @return string
     */
    abstract public function getInputCheck();

    /**
     *
     * @return string
     */
    abstract public function getValidation();
}