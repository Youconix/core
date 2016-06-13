<?php
namespace youconix\core\helpers\html;

/**
 * HTML parent class
 */
abstract class HtmlItem extends \youconix\core\helpers\html\CoreHtmlItem
{

	protected $a_eventName = array();

	protected $a_eventValue = array();

	protected $s_style = '';

	protected $s_class = '';

	protected $s_javascript = '';

	/**
	 * Destructor
	 */
	public function __destruct()
	{
		$this->a_eventName = null;
		$this->a_eventValue = null;
		$this->s_style = null;
		$this->s_class = null;
		$this->s_javascript = null;

		parent::__destruct();
	}

	/**
	 * Sets the given event on the item
	 *
	 * @param string $s_name
	 *            event name
	 * @param string $s_value
	 *            event value
	 */
	public function setEvent($s_name, $s_value)
	{
		$this->a_eventName[] = $s_name;
		$this->a_eventValue[] = $s_value;

		return $this;
	}

	/**
	 * Sets the style on the item.
	 * Adds the style if a style is allready active
	 *
	 * @param string $s_style
	 *            style
	 */
	public function setStyle($s_style)
	{
		if (! empty($this->s_style))
			$this->s_style .= '; ';
			$this->s_style .= $s_style;

			return $this;
	}

	/**
	 * Sets the class on the item.
	 * Adds the class if a class is allready active
	 *
	 * @param string $s_class
	 *            class
	 */
	public function setClass($s_class)
	{
		if (! empty($this->s_class))
			$this->s_class .= ' ';
			$this->s_class .= $s_class;

			return $this;
	}

	/**
	 * Sets the value on the item.
	 * Adds the value if a value is allready set
	 *
	 * @param string $s_value
	 *            value,  also accepts \youconix\core\helpers\html\CoreHtmlItem
	 */
	public function setValue($s_value)
	{
		$s_value = $this->parseContent($s_value);

		$this->s_value .= $s_value;

		return $this;
	}

	/**
	 * Generates the (X)HTML-code
	 *
	 * @see \youconix\core\helpers\htmlCoreHtmlItem::generateItem()
	 * @return string The (X)HTML code
	 */
	public function generateItem()
	{
		$this->s_javascript = '';
		for ($i = 0; $i < count($this->a_eventName); $i ++) {
			$this->s_javascript .= $this->a_eventName[$i] . '="' . $this->a_eventValue[$i] . '" ';
		}

		if (! empty($this->s_style)) {
			$this->s_between .= 'style="' . trim($this->s_style) . '"';
		}
		if (! empty($this->s_class)) {
			$this->s_between .= ' class="' . trim($this->s_class) . '"';
		}
		if (! empty($this->s_javascript)) {
			$this->s_between .= ' ' . trim($this->s_javascript);
		}

		return parent::generateItem();
	}
}