<?php
namespace youconix\core\helpers\html;

class Input extends CoreHTML_Input
{

    protected $s_type;
    protected $s_htmlType;

    /**
     * Generates a new input element
     *
     * @param string $s_name
     *            The name of the field
     * @param string $s_type
     *            The type of the field (text|password|hidden|email)
     * @param string $s_value
     *            The default text of the field
     * @param string $s_htmlType
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
     * @param string $s_type
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
     * @see \youconix\core\helpers\html\HtmlFormItem::generateItem()
     * @return string The (X)HTML code
     */
    public function generateItem()
    {
        $this->s_tag = str_replace('{type}', $this->s_type, $this->s_tag);
        
        return parent::generateItem();
    }
}