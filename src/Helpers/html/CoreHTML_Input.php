<?php
namespace youconix\core\helpers\html;

abstract class CoreHTML_Input extends \youconix\core\helpers\html\HtmlFormItem
{
	protected $a_errorMessages = array();
	protected $bo_required = false;

	/**
	 * Constructor
	 *
	 * @param string $s_name
	 *            The name of the field
	 * @param string $s_type
	 *            The type of the field
	 * @param string $s_htmlType
	 *            type
	 */
	public function __construct($s_name, $s_type, $s_htmlType)
	{
		$this->s_name = $s_name;
		$this->s_type = $s_type;
		$this->setHtmlType($s_htmlType);
	}

	/**
	 * Sets the field as required
	 */
	public function setRequired(){
		$this->bo_required = true;
	}

	/**
	 * Sets the error message
	 * 
	 * @param string $s_name
	 * @param string $s_message
	 */
	public function setErrorMessage($s_name,$s_message){
		$this->a_errorMessages[$s_name] = $s_message;
	}

	/**
	 * Generates the (X)HTML-code
	 *
	 * @see \youconix\core\helpers\html\HtmlFormItem::generateItem()
	 * @return string The (X)HTML code
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