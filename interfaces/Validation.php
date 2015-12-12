<?php

interface Validation
{

    /**
     * Validates the given email address
     *
     * @param string $s_email
     *            The email address
     * @return boolean True if the email address is valid, otherwise false
     */
    public function checkEmail($s_email);

    /**
     * Validates the given URI
     *
     * @param string $s_uri
     *            The URI
     * @return boolean True if the URI is valid, otherwise false
     */
    public function checkURI($s_uri);

    /**
     * Validates the given dutch postal address
     *
     * @param string $s_value
     *            The postal address
     * @return boolean True if the postal address is valid, otherwise false
     */
    public function checkPostalNL($s_value);

    /**
     * Validates the given belgium postal address
     *
     * @param string $s_value
     *            The postal address
     * @return boolean True if the postal address is valid, otherwise false
     */
    public function checkPostalBE($i_value);

    /**
     * Validates the IP address
     *
     * @param string $s_value
     *            The IPv4 or IPv6 address
     * @return boolean True if the address is valid
     */
    public function validateIP($s_value);

    /**
     * Performs the validation
     *
     * @return boolean True if the fields are valid
     */
    public function validate($a_validation, $a_collection);

    /**
     * Validates the given field
     *
     * @param string $s_key
     *            The field name
     * @param mixed $field
     *            The field value
     * @param string $s_rules
     *            The validation rules
     */
    public function validateField($s_key, $field, $s_rules);

    /**
     * Returns the errors after validation
     *
     * @return array The errors
     */
    public function getErrors();
}