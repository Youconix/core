<?php
namespace youconix\core\helpers\html;

class JavascriptLink extends \youconix\core\helpers\html\CoreHtmlItem
{

	/**
	 * Generates a new Javascript link element
	 *
	 * @param string $s_link
	 *            The url of the link
	 * @param string $s_htmlType
	 *            type
	 */
	public function __construct($s_link, $s_htmlType)
	{
		$s_type = ' type="text/javascript"';
		if ($s_htmlType == 'html5') {
			$s_type = '';
		}
		$this->setHtmlType($s_htmlType);

		$this->s_tag = '<script src="' . $s_link . '"' . $s_type . ' {between}></script>' . "\n";
	}
}