<?php 
namespace youconix\core\helpers\html;

class Footer extends \youconix\core\helpers\html\Div
{

	/**
	 * Generates a new footer element
	 *
	 * @param string $s_content
	 *            content
	 */
	public function __construct($s_content)
	{
		$this->s_tag = "<footer {between}>\n{value}\n</footer>\n";

		$this->setContent($s_content);
	}
}