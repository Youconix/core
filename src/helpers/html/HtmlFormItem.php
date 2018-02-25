<?php
namespace youconix\core\helpers\html;

/**
 * HTML form parent class
 */
abstract class HtmlFormItem extends \youconix\core\helpers\html\HtmlItem
{

	protected $bo_disabled = false;

	protected $s_name;

	/**
	 * Destructor
	 */
	public function __destruct()
	{
		$this->bo_disabled = null;
		$this->s_name = null;

		parent::__destruct();
	}

	/**
	 * Enables or disables the item
	 *
	 * @param boolean $bo_disabled
	 *            to true to disable the item
	 */
	public function setDisabled($bo_disabled)
	{
		$this->bo_disabled = $bo_disabled;
	}

	/**
	 * Generates the (X)HTML-code
	 *
	 * @see HtmlItem::generateItem()
	 * @return string The (X)HTML code
	 */
	public function generateItem()
	{
		if ($this->bo_disabled) {
			$this->s_between .= ' disabled="disabled"';
		}

		$this->s_tag = str_replace('{name}', $this->s_name, $this->s_tag);

		return parent::generateItem();
	}
}