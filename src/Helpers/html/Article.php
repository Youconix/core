<?php 
namespace youconix\core\helpers\html;

class Article extends \youconix\core\helpers\html\Div
{

	/**
	 * Generates a new article element
	 *
	 * @param string $s_content
	 *            content
	 */
	public function __construct($s_content)
	{
		$this->s_tag = "<article {between}>\n{value}\n</article>\n";

		$this->setContent($s_content);
	}
}