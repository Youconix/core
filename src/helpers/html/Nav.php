<?php
namespace youconix\core\helpers\html;

class Nav extends \youconix\core\helpers\html\Div
{

	/**
	 * Generates a new nav element
	 *
	 * @param string $s_content
	 *            content
	 */
	public function __construct($s_content)
	{
		$this->s_tag = "<nav {between}>\n{value}\n</nav>\n";

		$this->setContent($s_content);
	}
}