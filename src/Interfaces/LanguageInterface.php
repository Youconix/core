<?php

interface LanguageInterface
{

    /**
     * Gets the name belonging to the language code
     *
     * @param string $code
     *            The language code
     * @return string The language name
     */
    public function getLanguageText($code);

    /**
     * Returns the language codes
     *
     * @return array The codes
     */
    public function getLanguageCodes();

    /**
     * Sets the language
     *
     * @param string $language
     *            code
     * @throws IOException when the language code does not exist
     */
    public function setLanguage($language);

    /**
     * Returns the set language
     *
     * @return string The set language
     */
    public function getLanguage();

    /**
     * Returns the set encoding
     *
     * @return string The set encoding
     */
    public function getEncoding();

    /**
     * Gives the asked part of the loaded file
     *
     * @param string $path
     *            The path to the language-part
     * @return string The content of the requested part
     * @throws IOException when the path does not exist
     */
    public function get($path);

    /**
     * Changes the language-values with the given values
     * Collects the text from the language file via the path
     *
     * @param string $path
     *            The path to the language-part
     * @param array $fields
     *            accepts also a string
     * @param array $values
     *            accepts also a string
     * @return string changed language-string
     * @throws IOException when the path does not exist
     */
    public function insertPath($path, $fields, $values);

    /**
     * Changes the language-values with the given values
     *
     * @param string $text
     * @param array $fields
     *            accepts also a string
     * @param array $values
     *            accepts also a string
     * @return string changed language-string
     */
    public function insert($text, $fields, $values);

    /**
     * Checks of the given part of the loaded file exists
     *
     * @param string $path
     *            The path to the language-part
     * @return boolean, true if the part exists otherwise false
     */
    public function exists($path);
}