<?php
namespace youconix\core\helpers\html;

class Radio extends \youconix\core\helpers\html\CoreHTML_Input
{

	protected $bo_checked = false;

	/**
	 * Generates a new radio button element
	 *
	 * @param string $s_name
	 * @param string $s_value
	 * @param string $s_htmlType
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
	 * @param string $s_name
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
	 * @see \youconix\core\helpers\html\HtmlFormItem::generateItem()
	 * @return string The (X)HTML code
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