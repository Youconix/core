<?php
namespace core\helpers\html;

class InputFactory
{

    private static $_instance;

    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new InputFactory();
        }
        
        return self::$_instance;
    }

    protected function __clone()
    {}

    /**
     * Generates a new text field
     *
     * @param String $s_name
     *            The name of the field
     * @param String $s_type
     *            The type of the field
     * @param String $s_value
     *            The default text of the field
     * @param String $s_htmlType
     *            type
     * @throws \Exception If the type is invalid
     * @return Input The field
     */
    public function input($s_name, $s_type, $s_value, $s_htmlType)
    {
        return new Input($s_name, $s_type, $s_value, $s_htmlType);
    }

    /**
     * Creates a range slider
     *
     * @param String $s_name
     *            The name
     * @param String $s_value
     *            The value
     * @return Range The slider
     */
    public function range($s_name, $i_value)
    {
        return new Range($s_name, $i_value);
    }

    /**
     * Creates a new number field
     *
     * @param String $s_name
     *            The name
     * @param String $s_value
     *            The value
     * @return Number The field
     */
    public function number($s_name, $i_value)
    {
        return new Number($s_name, $i_value);
    }

    /**
     * Creates a new date field
     *
     * @param String $s_name
     *            The name
     * @param String $s_value
     *            The value
     * @return Date The field
     */
    public function date($s_name, $i_value)
    {
        return new Date($s_name, $i_value);
    }

    /**
     * Creates a new date and time field
     *
     * @param String $s_name
     *            The name of the field
     * @param bool $bo_local
     *            Set to true to localize the field
     * @return Datetime The field
     */
    public function datetime($s_name, $bo_local)
    {
        return new Datetime($s_name, $bo_local);
    }

    /**
     * Generates a new button
     *
     * @param String $s_name
     *            The name of the button
     * @param String $s_type
     *            The type of the button
     * @param String $s_value
     *            The default text of the button
     * @param String $s_htmlType
     *            type
     * @throws \Exception If the type is invalid
     */
    public function button($s_name, $s_type, $s_value, $s_htmlType)
    {
        return new Button($s_name, $s_type, $s_value, $s_htmlType);
    }

    /**
     * Generates a new radio button element
     *
     * @param String $s_name            
     * @param String $s_value            
     * @param String $s_htmlType
     *            type
     * @return Radio The radio button
     */
    public function radio($s_name, $s_value, $s_htmlType)
    {
        return new Radio($s_name, $s_value, $s_htmlType);
    }

    /**
     * Generates a new checkbox element
     *
     * @param String $s_name            
     * @param String $s_value
     *            value
     * @param String $s_htmlType
     *            type
     * @return Checkbox The checkbox
     */
    public function checkbox($s_name, $s_value, $s_htmlType)
    {
        return new Checkbox($s_name, $s_value, $s_htmlType);
    }

    /**
     * Generates a multiply row text input field
     *
     * @param String $s_name
     *            The name of the textarea
     * @param String $s_value
     *            The default text of the textarea, optional
     * @return Textarea The Textarea object
     */
    public function textarea($s_name, $s_value = '')
    {
        return new Textarea($s_name, $s_value);
    }

    /**
     * Generates a select list
     *
     * @param String $s_name
     *            The name of the select list
     * @return Select The Select list object
     */
    public function select($s_name)
    {
        return new Select($s_name);
    }
}

abstract class CoreHTML_Input extends HtmlFormItem
{
    protected $a_errorMessages = array();
    protected $bo_required = false;
    
    /**
     * Constructor
     *
     * @param String $s_name
     *            The name of the field
     * @param String $s_type
     *            The type of the field
     * @param String $s_htmlType
     *            type
     */
    public function __construct($s_name, $s_type, $s_htmlType)
    {
        $this->s_name = $s_name;
        $this->s_type = $s_type;
        $this->setHtmlType($s_htmlType);
    }
    
    public function setRequired(){
        $this->bo_required = true;
    }
    
    public function setErrorMessage($s_name,$s_message){
        $this->a_errorMessages[$s_name] = $s_message;
    }
    
    /**
     * Generates the (X)HTML-code
     *
     * @see HtmlFormItem::generateItem()
     * @return String The (X)HTML code
     */
    public function generateItem()
    {
        if( $this->bo_required ){
            $this->s_between .= ' required ';
        }
        foreach($this->a_errorMessages AS $s_name => $s_message){
            if( $s_name == 'default' ){
                $this->s_between .= 'data-validation="'.$s_message.'" ';
            }
            else {
                $this->s_between .= 'data-validation-'.$s_name.'="'.$s_message.'" ';
            }
        }
    
        return parent::generateItem();
    }
}

class Input extends CoreHTML_Input
{

    protected $s_type;
    protected $s_htmlType;

    /**
     * Generates a new input element
     *
     * @param String $s_name
     *            The name of the field
     * @param String $s_type
     *            The type of the field (text|password|hidden|email)
     * @param String $s_value
     *            The default text of the field
     * @param String $s_htmlType
     *            The type of markup language
     */
    public function __construct($s_name, $s_type, $s_value, $s_htmlType)
    {
        $this->s_htmlType = $s_htmlType;
        $this->checkType($s_type);
        
        parent::__construct($s_name, $s_type, $s_htmlType);
        $this->setValue($s_value);
        
        if ($s_htmlType == 'xhtml') {
            $this->s_tag = '<input type="{type}" name="{name}" {between} value="{value}"/>';
        } else {
            $this->s_tag = '<input type="{type}" name="{name}" {between} value="{value}">';
        }
    }

    /**
     * Checks the type
     *
     * @param String $s_type
     *            The type of the field     *            
     * @throws \Exception If the type is invalid
     */
    protected function checkType($s_type)
    {
        $a_types = array(
            'text',
            'hidden',
            'password'
        );
        if ($this->s_htmlType == 'html5') {
            $a_types = array_merge($a_types, array(
                'search',
                'email',
                'url',
                'tel',
                'date',
                'month',
                'week',
                'time',
                'color'
            ));
        }
        if (! in_array($s_type, $a_types)) {
            throw new \Exception('Invalid input type ' . $s_type);
        }
    }

    /**
     * Generates the (X)HTML-code
     *
     * @see HtmlFormItem::generateItem()
     * @return String The (X)HTML code
     */
    public function generateItem()
    {
        $this->s_tag = str_replace('{type}', $this->s_type, $this->s_tag);
        
        return parent::generateItem();
    }
}

class Datetime extends CoreHTML_Input
{

    /**
     * Creates a new date and time field
     *
     * @param String $s_name
     *            The name of the field
     * @param bool $bo_local
     *            Set to true to localize the field
     */
    public function __construct($s_name, $bo_local)
    {
        $this->s_name = $s_name;
        
        if ($bo_local) {
            $this->s_tag = '<input type="datetime-local" name="{name}" {between}>';
        } else {
            $this->s_tag = '<input type="datetime" name="{name}" {between}>';
        }
    }
}

class Range extends HtmlFormItem
{

    private $i_min;

    private $i_max;

    /**
     * Creates a range slider
     *
     * @param String $s_name
     *            The name
     * @param String $s_value
     *            The value
     */
    public function __construct($s_name, $s_value)
    {
        $this->s_name = $s_name;
        $this->setHtmlType('html5');
        $this->setValue($s_value);
        
        $this->s_tag = '<input type="range" name="{name}"{min}{max}{between} value="{value}">';
    }

    /**
     * Sets the minimun value
     *
     * @param int $i_value
     *            The value
     */
    public function setMinimun($i_value)
    {
        $this->i_min = $i_value;
        
        return $this;
    }

    /**
     * Sets the maximun value
     *
     * @param int $i_value
     *            The value
     */
    public function setMaximun($i_value)
    {
        $this->i_max = $i_value;
        
        return $this;
    }

    /**
     * Generates the (X)HTML-code
     *
     * @see HtmlFormItem::generateItem()
     * @return String The (X)HTML code
     */
    public function generateItem()
    {
        (! is_null($this->i_min)) ? $this->i_min = ' min="' . $this->i_min . '"' : $this->i_min = '';
        (! is_null($this->i_min)) ? $this->i_max = ' max="' . $this->i_max . '"' : $this->i_max = '';
        
        $this->s_tag = str_replace(array(
            '{min}',
            '{max}'
        ), array(
            $this->i_min,
            $this->i_max
        ), $this->s_tag);
        
        return parent::generateItem();
    }
}

class Date extends Range
{

    /**
     * Creates a new date field
     *
     * @param String $s_name
     *            The name
     * @param String $s_value
     *            The value
     */
    public function __construct($s_name, $s_value)
    {
        parent::__construct($s_name, $s_value);
        
        $this->s_tag = '<input type="date" name="{name}"{min}{max}{between} value="{value}">';
    }
}

class Number extends Range
{

    private $i_step;

    /**
     * Creates a new number field
     *
     * @param String $s_name
     *            The name
     * @param String $s_value
     *            The value
     */
    public function __construct($s_name, $s_value)
    {
        parent::__construct($s_name, $s_value);
        
        $this->s_tag = '<input type="number" name="{name}"{min}{max}{step}{between} value="{value}">';
    }

    /**
     * Sets the step size
     *
     * @param int $i_step
     *            The size
     */
    public function setStep($i_step)
    {
        $this->i_step = $i_step;
        
        return $this;
    }

    /**
     * Generates the (X)HTML-code
     *
     * @see HtmlFormItem::generateItem()
     * @return String The (X)HTML code
     */
    public function generateItem()
    {
        (! is_null($this->i_step)) ? $this->i_step = ' step="' . $this->i_step . '"' : $this->i_step = '';
        
        $this->s_tag = str_replace('{step}', $this->i_step, $this->s_tag);
        
        return parent::generateItem();
    }
}

class Button extends Input
{

    /**
     * Checks the type
     *
     * @param String $s_type
     *            The type of the field
     * @throws \Exception If the type is invalid
     */
    protected function checkType($s_type)
    {
        if (! in_array($s_type, array(
            'button',
            'reset',
            'submit'
        ))) {
            throw new \Exception('invalid button type ' . $s_type);
        }
    }
}

class Radio extends CoreHTML_Input
{

    protected $bo_checked = false;

    /**
     * Generates a new radio button element
     *
     * @param String $s_name            
     * @param String $s_value            
     * @param String $s_htmlType
     *            type
     */
    public function __construct($s_name, $s_value, $s_htmlType)
    {
        $this->s_value = $s_value;
        $this->s_name = $s_name;
        $this->setHtmlType($s_htmlType);
        
        if ($s_htmlType == 'xhtml') {
            $this->s_tag = '<input type="radio" name="{name}"{value}{checked} {between}/>';
        } else {
            $this->s_tag = '<input type="radio" name="{name}"{value}{checked} {between}>';
        }
    }

    /**
     * Disabled
     */
    public function setValue($s_value)
    {}

    /**
     * Sets the name
     *
     * @param String $s_name
     *            The value of the radio button
     */
    public function setName($s_name)
    {
        parent::setValue($s_name);
        
        return $this;
    }

    /**
     * Sets the radio button on checked
     */
    public function setChecked()
    {
        $this->bo_checked = true;
        
        return $this;
    }

    /**
     * Generates the (X)HTML-code
     *
     * @see HtmlFormItem::generateItem()
     * @return String The (X)HTML code
     */
    public function generateItem()
    {
        ($this->bo_checked) ? $s_checked = ' checked="checked"' : $s_checked = '';
        
        if (! empty($this->s_value)) {
            $this->s_tag = str_replace('{value}', ' value="{value}"', $this->s_tag);
        }
        
        $this->s_tag = str_replace('{checked}', $s_checked, $this->s_tag);
        
        return parent::generateItem();
    }
}

class Checkbox extends Radio
{

    /**
     * Generates a new checkbox element
     *
     * @param String $s_name            
     * @param String $s_value
     *            value
     * @param String $s_htmlType
     *            type
     */
    public function __construct($s_name, $s_value, $s_htmlType)
    {
        $this->s_value = $s_value;
        $this->s_name = $s_name;
        $this->setHtmlType($s_htmlType);
        
        if ($s_htmlType == 'xhtml') {
            $this->s_tag = '<input type="checkbox" name="{name}"{value}{checked} {between}/>';
        } else {
            $this->s_tag = '<input type="checkbox" name="{name}"{value}{checked} {between}>';
        }
    }

    /**
     * Sets the name
     *
     * @param String $s_name
     *            The name of the checkbox
     */
    public function setName($s_name)
    {
        parent::setValue($s_name);
        
        return $this;
    }
}

class Textarea extends HtmlFormItem
{

    private $i_rows = 0;

    private $i_cols = 0;

    /**
     * Generates a new textarea item
     *
     * @param String $s_name            
     * @param String $s_value            
     */
    public function __construct($s_name, $s_value)
    {
        $this->s_name = $s_name;
        $this->s_value = $this->parseContent($s_value);
        
        $this->s_tag = '<textarea rows="{rows}" cols="{cols}" name="{name}" {between}>{value}</textarea>';
    }

    /**
     * Sets the number of rows
     *
     * @param int $i_rows
     *            of rows
     */
    public function setRows($i_rows)
    {
        if ($i_rows >= 0)
            $this->i_rows = $i_rows;
        
        return $this;
    }

    /**
     * Sets the number of cols
     *
     * @param int $i_cols
     *            of cols
     */
    public function setCols($i_cols)
    {
        if ($i_cols >= 0)
            $this->i_cols = $i_cols;
        
        return $this;
    }

    /**
     * Generates the (X)HTML-code
     *
     * @see HtmlFormItem::generateItem()
     * @return String The (X)HTML code
     */
    public function generateItem()
    {
        $this->s_tag = str_replace(array(
            '{rows}',
            '{cols}',
            '{name}'
        ), array(
            $this->i_rows,
            $this->i_cols,
            $this->s_name
        ), $this->s_tag);
        
        return parent::generateItem();
    }
}

class Select extends HtmlFormItem
{

    private $a_options;

    /**
     * Generates a new select element
     *
     * @param String $s_name
     *            The name of the select list
     */
    public function __construct($s_name)
    {
        $this->s_tag = "<select {name} {between}>\n{value}</select>\n";
        
        $this->a_options = array();
        $this->s_name = 'name="' . $s_name . '"';
    }

    /**
     * Sets the name of the select list
     *
     * @param String $s_name
     *            The name
     */
    public function setValue($s_name)
    {
        $this->s_name = 'name="' . $s_name . '"';
        
        return $this;
    }

    /**
     * Sets a option to the select list
     *
     * @param String $s_value
     *            The value displayed
     * @param Boolean $bo_selected
     *            True if the option is default selected, otherwise false
     * @param String $s_hiddenValue
     *            value different from the display value, optional
     */
    public function setOption($s_value, $bo_selected, $s_hiddenValue = '')
    {
        $this->a_options[] = array(
            'value' => $s_value,
            'selected' => $bo_selected,
            'hidden' => $s_hiddenValue
        );
        
        return $this;
    }

    /**
     * Generates the (X)HTML-code
     *
     * @see HtmlFormItem::generateItem()
     * @return String The (X)HTML code
     */
    public function generateItem()
    {
        $this->s_tag = str_replace('{name}', $this->s_name, $this->s_tag);
        
        foreach ($this->a_options as $a_option) {
            $a_option['selected'] ? $s_selected = ' selected="selected"' : $s_selected = '';
            ! empty($a_option['hidden']) ? $s_keyValue = ' value="' . $a_option['hidden'] . '"' : $s_keyValue = '';
            
            $this->s_value .= '<option' . $s_keyValue . $s_selected . '>' . $a_option['value'] . "</option>\n";
        }
        
        return parent::generateItem();
    }
}