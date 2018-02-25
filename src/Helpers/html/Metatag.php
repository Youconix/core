<?php
namespace youconix\core\helpers\html;

class Metatag extends \youconix\core\helpers\html\CoreHtmlItem
{

	/**
	 * Generates a new metatag element
	 *
	 * @param string $s_name
	 *            The name of the metatag
	 * @param string $s_content
	 *            The content of the metatag
	 * @param string $s_scheme
	 *            The scheme of the metatag,optional
	 * @param string $s_htmlType
	 *            type
	 */
	public function __construct($s_name, $s_content, $s_scheme, $s_htmlType)
	{
		$this->setHtmlType($s_htmlType);
		if (! empty($s_scheme))
			$s_scheme = ' scheme="' . $s_scheme . ' ';

			$s_pre = 'name';
			if (in_array($s_name, array(
					'refresh',
					'charset',
					'expires'
			)))
				$s_pre = 'http-equiv';

				if ($s_htmlType == 'xhtml') {
					$this->s_tag = '<meta' . $s_scheme . ' ' . $s_pre . '="' . $s_name . '" content="' . $s_content . '" {between}/>' . "\n";
				} else {
					$this->s_tag = '<meta' . $s_scheme . ' ' . $s_pre . '="' . $s_name . '" content="' . $s_content . '" {between}>' . "\n";
				}
	}
}