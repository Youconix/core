<?php

interface CacheInterface
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
     * @param string $output
     *            The rendered page
     */
    public function writeCache($output);

    /**
     * Clears the given page from the site cache
     *
     * @param string $page
     *            The page ($_SERVER['REQUEST_URI'])
     */
    public function clearPage($page);

    /**
     * Clears the language cache (.mo)
     */
    public function cleanLanguageCache();

    /**
     * Clears the site cache
     */
    public function clearSiteCache();

    /**
     * Returns the no cache pages
     *
     * @return array The pages
     */
    public function getNoCachePages();

    /**
     * Adds a no-cache page
     *
     * @param string $page
     * @return int
     */
    public function addNoCachePage($page);

    /**
     * Deletes the given no-cache page
     *
     * @param int $id
     *            The page ID
     */
    public function deleteNoCache($id);
}