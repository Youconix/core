<?php
namespace youconix\core\helpers\html;

class Javascript extends \youconix\core\helpers\html\CoreHtmlItem
{

	/**
	 * Generates a new Javascript tags element
	 *
	 * @param string $s_javascript
	 *            The Javascript code
	 * @param string $s_htmlType
	 *            type
	 */
	public function __construct($s_javascript, $s_htmlType)
	{
		$s_type = ' type="text/javascript"';
		if ($s_htmlType == 'html5') {
			$s_type = '';
		}
		$this->setHtmlType($s_htmlType);

		$this->s_tag .= "<script" . $s_type . ">\n<!--\n" . $s_javascript . "\n//-->\n</script>\n";
	}
}