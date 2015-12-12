<?php
namespace youconix\core\helpers\html;

class ListItem extends \youconix\core\helpers\html\HtmlItem
{

	/**
	 * Generates a new list item element
	 *
	 * @param string/CoreHtmLItem $s_content
	 *            content
	 */
	public function __construct($s_content)
	{
		$this->s_tag = '<li {between}>' . $this->parseContent($s_content) . '</li>';
	}
}