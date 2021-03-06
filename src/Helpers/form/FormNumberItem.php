<?php
namespace youconix\core\helpers\form;

/**
 * Form number generator
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 2.0
 *       
 */
class FormNumberItem extends \youconix\core\helpers\form\FormItem
{

    protected $i_min = null;

    protected $i_max = null;

    protected $i_step = null;

    /**
     *
     * @return int
     */
    public function getMax()
    {
        return $this->i_max;
    }

    /**
     *
     * @return int
     */
    public function getMin()
    {
        return $this->i_min;
    }

    /**
     *
     * @return int
     */
    public function getStep()
    {
        return $this->i_step;
    }

    /**
     *
     * @param int $i_max            
     */
    public function setMax($i_max)
    {
        $this->i_max = $i_max;
    }

    /**
     *
     * @param int $i_min            
     */
    public function setMin($i_min)
    {
        $this->i_min = $i_min;
    }

    /**
     *
     * @param int $i_step            
     */
    public function setStep($i_step)
    {
        $this->i_step = $i_step;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \youconix\core\helpers\form\FormItem::generate()
     */
    public function generate($obj_data)
    {
        $s_name = $this->getName();
        $factory = $this->generator->getInputFactory();
        
        if ($this->getType() == 'number') {
            $item = $factory->number($s_name, $s_value);
        } else {
            $item = $factory->range($s_name, $i_value);
        }
        
        if (! is_null($this->getMin())) {
            $item->setMinimun($item->getMin());
        }
        if (! is_null($this->getMax())) {
            $item->setMaximun($this->getMax());
        }
        if (! is_null($field->getStep())) {
            $item->setStep($field->getStep());
        }
        
        $s_getter = 'get' . ucfirst($s_name);
        (method_exists($obj_data, $s_getter)) ? $s_value = $obj_data->$s_getter : $s_value = $this->s_default;
        $item->setValue($s_value);
        
        if ($this->isRequired()) {
            $item->setRequired();
        }
        foreach ($this->getErrorMessages() as $s_name => $s_message) {
            $item->setErrorMessage($s_name, $s_message);
        }
        
        return $item->generateItem();
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \youconix\core\helpers\form\FormItem::getInputChecks()
     */
    public function getInputChecks()
    {
        if (strpos($this->getPattern(), '.') !== false) {
            return 'float';
        }
        
        return 'int';
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \youconix\core\helpers\form\FormItem::getValidation()
     */
    public function getValidation()
    {
        if (strpos($item->getPattern(), '.') !== false) {
            $s_field = 'float';
        } else {
            $s_field = 'int';
        }
        
        if (! is_null($this->getMin())) {
            $s_field .= '|min:' . $this->getMin();
        }
        if (! is_null($this->getMax())) {
            $s_field .= '|min:' . $this->getMax();
        }
        
        if ($this->isRequired()) {
            $s_field .= '|required';
        }
        
        return $s_field;
    }
}