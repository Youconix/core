<?php

interface Settings
{

    const SSL_DISABLED = 0;

    const SSL_LOGIN = 1;

    const SSL_ALL = 2;

    const REMOTE = 'https://framework.youconix.nl/2/';

    const MAJOR = 2;

    /**
     * Checks if the config value exists
     *
     * @param string $path
     * @return boolean
     */
    public function exists($path);

    /**
     * Gives the asked config block
     *
     * @param string $path
     * @return string The content of the requested part
     * @throws \ConfigException
     * @return array The block
     */
    public function getBlock($path);

    /**
     * Returns the config value
     *
     * @param string $path
     * @return string The content of the requested part
     * @throws \ConfigException
     */
    public function get($path);
}