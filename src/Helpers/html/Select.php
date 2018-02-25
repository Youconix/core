<?php
namespace youconix\core\helpers\html;

class Select extends \youconix\core\helpers\html\HtmlFormItem
{

	protected $a_options;

	/**
	 * Generates a new select element
	 *
	 * @param string $s_name
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
	 * @param string $s_name
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
	 * @param string $s_value
	 *            The value displayed
	 * @param boolean $bo_selected
	 *            True if the option is default selected, otherwise false
	 * @param string $s_hiddenValue
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
	 * @see \youconix\core\helpers\html\HtmlFormItem::generateItem()
	 * @return string The (X)HTML code
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