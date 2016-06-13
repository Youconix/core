<?php
namespace youconix\core\helpers\form;

/**
 * Form generator
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 12.0
 *       
 */
abstract class FormGenerator
{
    protected $a_items = array();

    protected $obj_data;

    public function __construct(\youconix\core\helpers\HTML $generator, \Language $language)
    {
        $this->generator = $generator;
        $this->language = $language;
        
        $this->init();
    }

    /**
     * Inits the form generator
     *
     * @abstract
     *
     */
    protected function init()
    {
        trigger_error('Function init from core\helpers\form\FormGenerator must be overridden.', E_USER_ERROR);
    }

    /**
     *
     * @return array
     */
    public function getInputChecks()
    {
        $a_checks = array();
        foreach ($this->a_items as $item) {
            if (in_array($item->getType(), array(
                'number',
                'range'
            ))) {
                if (strpos($item->getPattern(), '.') !== false) {
                    $s_field = 'float';
                } else {
                    $s_field = 'int';
                }
            } else {
                $s_field = 'string-DB';
            }
        }
        
        return $a_checks;
    }

    /**
     *
     * @param \youconix\core\helpers\form\FormItem $item            
     * @return string
     */
    protected function validateItem($item)
    {
        $s_field = '';
        // Get type
        if (in_array($item->getType(), array(
            'number',
            'range'
        ))) {
            if (strpos($item->getPattern(), '.') !== false) {
                $s_field = 'float';
            } else {
                $s_field = 'int';
            }
            
            if (! is_null($item->getMin())) {
                $s_field .= '|min:' . $item->getMin();
            }
            if (! is_null($item->getMax())) {
                $s_field .= '|min:' . $item->getMax();
            }
        } else {
            $s_field = 'string-DB';
        }
        
        if ($item->isRequired()) {
            $s_field .= '|required';
        }
        if (! is_null($item->getValues())) {
            $s_field .= '|set:' . implode(',', $item->getValues());
        }
        
        return $s_field;
    }

    /**
     *
     * @return array
     */
    protected function getEditFields()
    {
        return $this->a_items;
    }

    /**
     *
     * @return array
     */
    protected function getAddFields()
    {
        return $this->a_items;
    }

    /**
     *
     * @return array
     */
    protected function getViewFields()
    {
        return $this->a_items;
    }

    /**
     *
     * @return string[]
     */
    public function getEditValidation()
    {
        $a_fields = $this->getEditFields();
        
        $a_checks = array();
        foreach ($a_fields as $item) {
            $a_checks[$item->getName()] = $this->validateItem($item);
        }
        
        return $a_checks;
    }

    /**
     *
     * @return string[]
     */
    public function getAddValidation()
    {
        $a_fields = $this->getAddFields();
        
        $a_checks = array();
        foreach ($a_fields as $item) {
            $a_checks[$item->getName()] = $this->validateItem($item);
        }
        
        return $a_checks;
    }

    /**
     *
     * @return string
     */
    public function createView()
    {
        $a_fields = $this->getViewFields();
        $s_html = '<table>
        <tbody>';
        foreach ($a_fields as $field) {
            $s_name = $field->getName();
            
            if ($field->getType() == 'checkbox') {
                $s_getter = 'is' . ucfirst($s_name);
                $s_value = $this->language->get('system/admin/users/no');
                if (method_exists($this->obj_data, $s_getter) && $this->obj_data->$s_getter) {
                    $s_value = $this->language->get('system/admin/users/yes');
                }
            } else {
                $s_getter = 'get' . ucfirst($s_name);
                (method_exists($this->obj_data, $s_getter)) ? $s_value = $this->obj_data->$s_getter : $s_value = '';
            }
            
            $s_html .= '<tr>
                <td>' . $field->getLabel() . '</td>
                <td>' . $s_value . '</td>
            </tr>';
        }
        $s_html .= '</tbody>
        </table>';
        
        return $s_html;
    }

    /**
     * 
     * @return string
     */
    public function createEdit()
    {
        $a_fields = $this->getEditFields();
        $s_html = '';
        foreach ($a_fields as $field) {
            $s_name = $field->getName();
            if ($field->getType() == 'checkbox') {
                $s_getter = 'is' . ucfirst($s_name);
                $s_value = false;
                if (method_exists($this->obj_data, $s_getter) && $this->obj_data->$s_getter) {
                    $s_value = true;
                }
            } else {
                $s_getter = 'get' . ucfirst($s_name);
                (method_exists($this->obj_data, $s_getter)) ? $s_value = $this->obj_data->$s_getter : $s_value = '';
            }
            
            $s_html .= '<fieldset>
                <label class="label" for="' . $s_name . '">' . $field->getLabel() . '</label>
                ' . $this->createField($field, $s_value) . '
            </fieldset>
            ';
        }
        
        return $s_html;
    }

    /**
     * 
     * @return string
     */
    public function createAdd()
    {
        $a_fields = $this->getAddFields();
        $s_html = '';
        foreach ($a_fields as $field) {
            $s_name = $field->name;
            $s_value = $field->default;
            
            $s_html .= '<fieldset>
                <label class="label" for="' . $s_name . '">' . $field->label . '</label>
                ' . $this->createField($field, $s_value) . '
            </fieldset>
            ';
        }
        
        return $s_html;
    }

    /**
     * 
     * @param string $field
     * @param string $s_value
     * @return string
     */
    protected function createField($field, $s_value)
    {
        $s_name = $field->name;
        $factory = $this->generator->getInputFactory();
        
        switch ($field->type) {
            case 'textarea':
                $item = $factory->textarea($s_name, $s_value);
                break;
            case 'radio':
            case 'checkbox':
                return $this->createFieldArray($field, $s_value);
            case 'date':
                $item = $factory->date($s_name, $s_value);
                break;
            
            case 'datetime':
                $item = $factory->datetime($s_name, $s_value);
                break;
            case 'number':
            case 'range':
                if ($field->getType() == 'number') {
                    $item = $factory->number($s_name, $s_value);
                } else {
                    $item = $factory->range($s_name, $i_value);
                }
                
                if (! is_null($field->getMin())) {
                    $item->setMinimun($item->getMin());
                }
                if (! is_null($field->getMax())) {
                    $item->setMaximun($item->getMax());
                }
                if (! is_null($field->getStep())) {
                    $item->setStep($field->getStep());
                }
                break;
            case 'select':
                $item = $factory->select($s_name);
                foreach ($field->getValues() as $s_key => $s_fieldValue) {
                    if (! is_numeric($s_key)) {
                        ($s_key == $s_value) ? $bo_selected = true : $bo_selected = false;
                        
                        $item->setOption($s_key, $bo_selected, $s_fieldValue);
                    } else {
                        ($s_fieldValue == $s_value) ? $bo_selected = true : $bo_selected = false;
                        $item->setOption($s_fieldValue, $bo_selected);
                    }
                }
            default:
                $item = $factory->input($s_name, 'string', $s_value, 'html-5');
                break;
        }
        
        if ($field->isRequired()) {
            $item->setRequired();
        }
        foreach ($field->getErrorMessages() as $s_name => $s_message) {
            $item->setErrorMessage($s_name, $s_message);
        }
        
        return $item->generateItem();
    }

    /**
     * 
     * @param string $field
     * @param array $a_values
     * @return string
     */
    protected function createFieldArray($field, $a_values)
    {
        $s_name = $field->getName();
        $factory = $this->generator->getInputFactory();
        
        $items = array();
        switch ($field->getType()) {
            case 'radio':
                foreach ($field->getValues() as $s_fieldValue) {
                    $item = $factory->radio($s_name, $s_fieldValue);
                    $items[] = $item;
                }
                break;
            
            case 'checkbox':
                foreach ($field->getValues() as $s_fieldValue) {
                    $items[] = $factory->checkbox($s_name, $s_fieldValue, 'html5');
                }
                if (count($items) == 0) {
                    $items[] = $factory->checkbox($s_name, $s_value, 'html5');
                }
                break;
        }
        
        $s_html = '';
        foreach ($items as $item) {
            if ($field->isRequired()) {
                $item->setRequired();
            }
            foreach ($field->getErrorMessages() as $s_name => $s_message) {
                $item->setErrorMessage($s_name, $s_message);
            }
            
            $s_html .= $item->generateItem();
        }
        
        return $s_html;
    }
    
    protected function createItem($s_name,$s_fieldName,$bo_required = true){
        $object = new \stdClass();
        $object->type = 'string';
        $object->name = $s_fieldName;
        $object->required = $bo_required;
        $object->label = null;
        $object->error_text = null;
        $object->min = null;
        $object->max = null;
        $object->step = null;
        $object->default = null;
        $object->values = null;
        $object->validation = null;
        
        $this->a_items[$s_name] = $object;
        
        return $object;
    }
}