<?php

interface Settings
{

    const SSL_DISABLED = 0;

    const SSL_LOGIN = 1;

    const SSL_ALL = 2;

    const REMOTE = 'http://framework.youconix.nl/2/';

    const MAJOR = 2;

    /**
     * Gives the asked part of the loaded file
     *
     * @param string $s_path
     *            The path to the language-part
     * @return string The content of the requested part
     * @throws \XMLException when the path does not exist
     */
    public function get($s_path);

    /**
     * Saves the settings file
     */
    public function save($s_file = '');
}