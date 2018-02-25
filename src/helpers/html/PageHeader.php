<?php 
namespace youconix\core\helpers\html;

class PageHeader extends \youconix\core\helpers\html\Div
{

	/**
	 * Generates a new header element
	 *
	 * @param string $s_content
	 *            content
	 */
	public function __construct($s_content)
	{
		$this->s_tag = "<header {between}>\n{value}\n</header>\n";

		$this->setContent($s_content);
	}
}
?>