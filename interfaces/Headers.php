<?php
interface Headers {
	/**
	 * Clears the headers
	 */
	public function clear();
	
	/**
	 * Sets the given content type
	 *
	 * @param String $s_contentType
	 *            content type
	 */
	public function contentType($s_contentType);
	
	/**
	 * Sets the javascript content type
	 */
	public function setJavascript();
	
	/**
	 * Sets the CSS content type
	 */
	public function setCSS();
	
	/**
	 * Sets the XML content type
	 */
	public function setXML();
	
	/**
	 * Sets the last modified header
	 *
	 * @param int $i_modified
	 *            modified time as a timestamp
	 */
	public function modified($i_modified);
	
	/**
	 * Sets the cache time, -1 for no cache
	 *
	 * @param int $i_cache
	 *            cache time in seconds
	 */
	public function cache($i_cache);
	
	/**
	 * Sets the content length
	 *
	 * @param int $i_length
	 *            length in bytes
	 */
	public function contentLength($i_length);
	
	/**
	 * Force downloads a file
	 * Program wil halt
	 *
	 * @param String $s_file
	 *            file location
	 * @param String $s_contentType
	 *            content type
	 */
	public function forceDownloadFile($s_file, $s_contentType);
	
	/**
	 * Force downloads the given content
	 * Program wil halt
	 *
	 * @param String $s_content
	 *            content to download
	 * @param String $s_contentType
	 *            content type
	 * @param String $s_name
	 *            name of the download
	 */
	public function forceDownloadContent($s_content, $s_contentType, $s_name);
	
	/**
	 * Sets a header
	 *
	 * @param String $s_key
	 *            header key
	 * @param String $s_content
	 *            header value
	 */
	public function setHeader($s_key, $s_content);
	
	/**
	 * Sends the 304 not modified header
	 */
	public function http304();
	
	/**
	 * Sends the 400 bad request header
	 */
	public function http400();
	
	/**
	 * Sends the 401 unauthorized header
	 */
	public function http401();
	
	/**
	 * Sends the 403 forbidden header
	 */
	public function http403();
	
	/**
	 * Sends the 404 not found header
	 */
	public function http404();
	
	/**
	 * Sends the 500 internal server header
	 */
	public function http500();
	
	/**
	 * Sends the 503 service unavailable header
	 */
	public function http503();
	
	/**
	 * Sends the 301 redirect header
	 * Program wil halt
	 *
	 * @param String $s_location
	 *            redirect location
	 */
	public function redirect($s_location);
	
	/**
	 * Returns if a force download was excecuted
	 *
	 * @return boolean True if the download was excecuted
	 */
	public function isForceDownload();
	
	/**
	 * Returns if a redirect was executed
	 *
	 * @return boolean True if a redirect was excequeted
	 */
	public function isRedirect();
	
	/**
	 * Returns the headers
	 *
	 * @return array headers
	 */
	public function getHeaders();
	
	/**
	 * Returns if the template should be skipped
	 * @return boolean
	 */
	public function skipTemplate();
	
	/**
	 * Imports the given headers
	 *
	 * @param array $a_headers
	 *            The headers
	 */
	public function importHeaders($a_headers);
	
	/**
	 * Sends the cached headers to the client
	 */
	public function printHeaders();
}