<?php

interface SecurityInterface
{

    /**
     * Checks for correct boolean value
     *
     * @param boolean $input
     * @return boolean checked value
     */
    public function secureBoolean($input);

    /**
     * Checks for correct int value
     *
     * @param int $input
     * @param boolean $positive
     * @return int The checked value
     */
    public function secureInt($input, $positive = false);

    /**
     * Checks for correct float value
     *
     * @param float $input
     * @param boolean $positive
     * @return float The checked value
     */
    public function secureFloat($input, $positive = false);

    /**
     * Disables code in the given string
     *
     * @param string $input
     * @return string The secured value
     */
    public function secureString($input);

    /**
     * Disables code in the given string for DB input
     *
     * @param string $input
     * @return string The secured value
     */
    public function secureStringDB($input);

    /**
     *
     * @param string $type Type of input (GET|POST|REQUEST)
     * @param array $declared
     * @return array The secured input data
     */
    public function secureInput($type, array $declared);

    /**
     * Prepares the decoding from AJAX requests
     *
     * @param string $value
     * @return string The decoded value
     */
    public function prepareJsDecoding($value);

    /**
     * Fixes the decodeUrl->htmlentities bug
     *
     * @param string $text
     * @return string correct decoded text
     */
    public function fixDecodeBug($text);
}