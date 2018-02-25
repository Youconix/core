<?php
namespace youconix\core\helpers\html;

class Section extends \youconix\core\helpers\html\Div
{

	/**
	 * Generates a new section element
	 *
	 * @param string $s_content
	 *            content
	 */
	public function __construct($s_content)
	{
		$this->s_tag = "<section {between}>\n{value}\n</section>\n";

		$this->setContent($s_content);
	}
}