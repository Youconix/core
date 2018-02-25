<?php

interface Cookie
{

    /**
     * Deletes the cookie with the given name and domain
     *
     * @param string $s_cookieName
     *            The name of the cookie
     * @param string $s_domain
     *            The domain of the cookie
     * @throws \Exception if the cookie does not exist
     */
    public function delete($s_cookieName, $s_domain);

    /**
     * Sets the cookie with the given name and data
     *
     * @param string $s_cookieName
     *            The name of the cookie
     * @param string $s_cookieData
     *            The data to put into the cookie
     * @param string $s_domain
     *            The domain the cookie schould work on, default /
     * @param string $s_url
     *            The URL the cookie schould work on, optional
     * @param int $i_secure
     *            1 if the cookie schould be https-only otherwise 0, optional
     * @return boolean True if the cookie has been set, false if it has not
     */
    public function set($s_cookieName, $s_cookieData, $s_domain, $s_url = "", $i_secure = 0);

    /**
     * Receives the content from the cookie with the given name
     *
     * @param string $s_cookieName
     *            The name of the cookie
     * @return string The requested cookie
     * @throws \Exception if the cookie does not exist
     */
    public function get($s_cookieName);

    /**
     * Checks if the given cookie exists
     *
     * @param string $s_cookieName
     *            The name of the cookie you want to check
     * @return boolean True if the cookie exists, false if it does not
     */
    public function exists($s_cookieName);
}