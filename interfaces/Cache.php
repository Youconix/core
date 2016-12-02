<?php

interface Cache
{

    /**
     * Checks the cache and displays it
     *
     * @return boolean False if no cache is present
     */
    public function checkCache();

    /**
     * Writes the renderd page to the cache
     *
     * @param string $s_output
     *            The rendered page
     */
    public function writeCache($s_output);

    /**
     * Clears the given page from the site cache
     *
     * @param string $s_page
     *            The page ($_SERVER['REQUEST_URI'])
     */
    public function clearPage($s_page);

    /**
     * Clears the language cache (.mo)
     */
    public function cleanLanguageCache();

    /**
     * Clears the site cache
     */
    public function clearSiteCache();

    /**
     * Clears the template parser cache files
     */
    public function clearTemplateCache();

    /**
     * Returns the no cache pages
     *
     * @return array The pages
     */
    public function getNoCachePages();

    /**
     * Adds a no-cache page
     *
     * @param string $s_page
     *            The page address
     */
    public function addNoCachePage($s_page);

    /**
     * Deletes the given no-cache page
     *
     * @param int $i_id
     *            The page ID
     */
    public function deleteNoCache($i_id);
}