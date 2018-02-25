<?php
namespace youconix\core\helpers\html;

class Stylesheet extends \youconix\core\helpers\html\CoreHtmlItem
{

	/**
	 * Generates the new CSS tags element
	 *
	 * @param string $s_css
	 *            The CSS code
	 * @param string $s_htmlType
	 *            type
	 */
	public function __construct($s_css, $s_htmlType)
	{
		$s_type = ' type="text/css"';
		if ($s_htmlType == 'html5') {
			$s_type = '';
		}
		$this->setHtmlType($s_htmlType);

		$this->s_tag = "<style" . $s_type . ">\n<!--\n" . $s_css . "\n-->\n</style>\n";
	}
}