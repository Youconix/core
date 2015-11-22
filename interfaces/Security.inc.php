<?php
interface Security {
	/**
	 * Checks for correct boolean value
	 *
	 * @param boolean $bo_input
	 *            The value to check
	 * @return boolean checked value
	 */
	public function secureBoolean($bo_input);
	
	/**
	 * Checks for correct int value
	 *
	 * @param int $i_input
	 *            The value to check
	 * @param boolean $bo_positive
	 *            Set to true for positive values only
	 * @return int The checked value
	 */
	public function secureInt($i_input, $bo_positive = false);
	
	/**
	 * Checks for correct float value
	 *
	 * @param float $fl_input
	 *            The value to check
	 * @param boolean $bo_positive
	 *            Set to true for positive values only
	 * @return float The checked value
	 */
	public function secureFloat($fl_input, $bo_positive = false);
	
	/**
	 * Disables code in the given string
	 *
	 * @param String $s_input
	 *            The value to make safe
	 * @return String The secured value
	 */
	public function secureString($s_input);
	
	/**
	 * Disables code in the given string for DB input
	 *
	 * @param String $s_input
	 *            The value to make safe
	 * @return String The secured value
	 */
	public function secureStringDB($s_input);
	
	/**
	 *
	 * @param String $s_type
	 *            Type of input (GET|POST|REQUEST)
	 * @param array $a_declared
	 *            De type declare array (init_get,init_post,init_request)
	 * @return array The secured input data
	 */
	public function secureInput($s_type, $a_declared);
	
	/**
	 * Prepares the decoding from AJAX requests
	 *
	 * @param String $s_value
	 *            The encoded value
	 * @return String The decoded value
	 */
	public function prepareJsDecoding($s_value);
	
	/**
	 * Fixes the decodeUrl->htmlentities bug
	 *
	 * @param String $s_text
	 *            input text
	 * @return String correct decoded text
	 */
	public function fixDecodeBug($s_text);
}