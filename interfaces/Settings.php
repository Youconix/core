<?php

interface Settings
{

    const SSL_DISABLED = 0;

    const SSL_LOGIN = 1;

    const SSL_ALL = 2;

    const REMOTE = 'https://framework.youconix.nl/2/';

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
     * Saves the value at the given place
     *
     * @param string $s_path
     *            The path to the language-part
     * @param string $s_content
     *            The content to save
     * @throws \XMLException when the path does not exist
     */
    public function set($s_path, $s_content);
    
    /**
     * Adds a new node
     *
     * @param string $s_path
     *            The new path
     * @param string $s_content
     *            The new content
     * @throws \XMLException If the path already exists
     */
    public function add($s_path, $s_content);
    
    /**
     * Checks of the given part of the loaded file exists
     *
     * @param string $s_path
     *            The path to the language-part
     * @return boolean, true if the part exists otherwise false
     */
    public function exists($s_path);

    /**
     * Saves the settings file
     */
    public function save($s_file = '');
}