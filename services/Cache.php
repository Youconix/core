<?php

namespace core\services;

/**
 * Miniature-happiness is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Miniature-happiness is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Miniature-happiness. If not, see <http://www.gnu.org/licenses/>.
 *
 * Cache service
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @version 1.0
 * @since 2.0
 */
class Cache extends \core\services\Service implements \Cache {
	/**
	 *
	 * @var \Builder
	 */
	protected $builder;
	
	/**
	 *
	 * @var \core\services\File
	 */
	protected $file;
	
	/**
	 *
	 * @var \Config
	 */
	protected $config;
	
	/**
	 *
	 * @var \Settings
	 */
	protected $settings;
	
	/**
	 *
	 * @var \Headers
	 */
	protected $headers;
	protected $s_language;
	protected $bo_cache;
	public function __construct(\core\services\File $file, \Config $config, \Headers $headers, \Builder $builder) {
		$this->config = $config;
		$this->file = $file;
		$this->settings = $config->getSettings ();
		$this->headers = $headers;
		$this->builder = $builder;
		
		$s_directory = $this->getDirectory ();
		if (! $this->file->exists ( $s_directory . 'site' )) {
			$this->file->newDirectory ( $s_directory . 'site' );
		}
	}
	
	/**
	 * Returns if the object schould be treated as singleton
	 *
	 * @return boolean True if the object is a singleton
	 */
	public static function isSingleton() {
		return true;
	}
	
	/**
	 * Returns the cache directory
	 *
	 * @return string The directory
	 */
	protected function getDirectory() {
		return $_SERVER ['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
	}
	
	/**
	 * Returns the cache file part seperator
	 *
	 * @return string The seperator
	 */
	protected function getSeperator() {
		return '===============|||||||||||||||||||||||=================';
	}
	
	/**
	 * Returns if the file should be cached
	 *
	 * @return boolean
	 */
	protected function shouldCache() {
		if (! is_null ( $this->bo_cache )) {
			return $this->bo_cache;
		}
		
		if (! $this->settings->exists ( 'cache/status' ) || $this->settings->get ( 'cache/status' ) != 1) {
			$this->bo_cache = false;
		} else {
			$s_page = $_SERVER ['REQUEST_URI'];
			$a_pages = explode ( '?', $s_page );
			
			$this->builder->select ( 'no_cache', 'id' )->getWhere ()->addOr ( array (
					'page',
					'page' 
			), array (
					's',
					's' 
			), array (
					$s_page,
					$a_pages [0] 
			) );
			$service_database = $this->builder->getResult ();
			if ($service_database->num_rows () > 0) {
				$this->bo_cache = false;
			} else {
				$this->bo_cache = true;
			}
		}
		
		return $this->bo_cache;
	}
	
	/**
	 * Checks the cache and displays it
	 *
	 * @return boolean False if no cache is present
	 */
	public function checkCache() {
		if ($_SERVER ['REQUEST_METHOD'] != 'GET' || ! $this->shouldCache ()) {
			return false;
		}
		
		$s_language = $this->config->getLanguage ();
		$s_directory = $this->getDirectory ();
		
		if (! $this->file->exists ( $s_directory . 'site' . DIRECTORY_SEPARATOR . $s_language )) {
			$this->file->newDirectory ( $s_directory . 'site' . DIRECTORY_SEPARATOR . $s_language );
			return false;
		}
		
		if (! $this->file->exists ( $s_directory . 'site' . DIRECTORY_SEPARATOR . $s_language )) {
			$this->file->newDirectory ( $s_directory . 'site' . DIRECTORY_SEPARATOR . $s_language );
			return false;
		}
		
		$s_target = $this->getCurrentPage ();
		
		if (! $this->file->exists ( $s_target )) {
			return false;
		}
		
		$a_file = explode ( $this->getSeperator (), $this->file->readFile ( $s_target ) );
		$i_timeout = $this->settings->get ( 'cache/timeout' );
		
		if ((time () - $a_file [0]) > $i_timeout) {
			$this->file->deleteFile ( $s_target );
			return false;
		}
		
		$this->displayCache ( $a_file );
	}
	
	/**
	 * Displays the cached page
	 *
	 * @param array $a_file
	 *        	The page parts
	 */
	protected function displayCache($a_file) {
		$a_headers = unserialize ( $a_file [1] );
		$this->headers->importHeaders ( $a_headers );
		$this->headers->printHeaders ();
		echo ($a_file [2]);
		die ();
	}
	
	/**
	 * Writes the renderd page to the cache
	 *
	 * @param string $s_output
	 *        	The rendered page
	 */
	public function writeCache($s_output) {
		if (! $this->shouldCache ()) {
			return;
		}
		
		$s_headers = serialize ( $this->headers->getHeaders () );
		
		$s_output = time () . $this->getSeperator () . $s_headers . $this->getSeperator () . $s_output;
		
		$s_language = $this->config->getLanguage ();
		$s_target = $this->getCurrentPage ();
		$this->file->writeFile ( $s_target, $s_output );
	}
	
	/**
	 * Returns the current page address
	 *
	 * @return string The address
	 */
	protected function getCurrentPage() {
		return $this->getAddress ( $_SERVER ['REQUEST_URI'] );
	}
	
	/**
	 * Returns the full address for the given page
	 *
	 * @param string $s_page
	 *        	The page ($_SERVER['REQUEST_URI'])
	 * @return string The address
	 */
	protected function getAddress($s_page) {
		return $this->getDirectory () . 'site' . DIRECTORY_SEPARATOR . $s_language . DIRECTORY_SEPARATOR . str_replace ( '/', '_', $s_page ) . '.html';
	}
	
	/**
	 * Clears the given page from the site cache
	 *
	 * @param string $s_page
	 *        	The page ($_SERVER['REQUEST_URI'])
	 */
	public function clearPage($s_page) {
		$s_page = $this->getAddress ( $s_page );
		
		if ($this->file->exists ( $s_page )) {
			$this->file->deleteFile ( $s_page );
		}
	}
	
	/**
	 * Clears the language cache (.mo)
	 */
	public function cleanLanguageCache() {
		if ($this->settings->exists ( 'language/type' ) && $this->settings->get ( 'language/type' ) == 'mo') {
			clearstatcache ();
		}
	}
	
	/**
	 * Clears the site cache
	 */
	public function clearSiteCache() {
		$s_dir = $this->getDirectory () . 'site';
		
		$a_files = $this->file->readDirectory ( $s_dir );
		foreach ( $a_files as $s_file ) {
			if ($s_file == '.' || $s_file == '.') {
				continue;
			}
			
			if (is_dir ( $s_dir . DIRECTORY_SEPARATOR . $s_file )) {
				$this->file->deleteDirectory ( $s_dir . DIRECTORY_SEPARATOR . $s_file );
			} else {
				$this->file->deleteFile ( $s_dir . DIRECTORY_SEPARATOR . $s_file );
			}
		}
	}
	
	/**
	 * Returns the no cache pages
	 *
	 * @return array The pages
	 */
	public function getNoCachePages() {
		$a_pages = array ();
		
		$this->builder->select ( 'no_cache', '*' );
		$service_database = $this->builder->getResult ();
		if ($service_database->num_rows () > 0) {
			$a_pages = $service_database->fetch_assoc ();
		}
		
		return $a_pages;
	}
	
	/**
	 * Adds a no-cache page
	 *
	 * @param string $s_page
	 *        	The page address
	 */
	public function addNoCachePage($s_page) {
		\core\Memory::type ( 'string', $s_page );
		
		if (substr ( $s_page, - 4 ) != '.php') {
			$s_page .= '.php';
		}
		
		if (! $this->file->exists ( NIV . $s_page )) {
			return;
		}
		
		$this->builder->select ( 'no_cache', 'id' )->getWhere ()->addAnd ( 'page', 's', $s_page );
		$service_database = $this->builder->getResult ();
		if ($service_database->num_rows () != 0) {
			return;
		}
		
		$this->builder->insert ( 'no_cache', 'page', 's', $s_page );
		$database = $this->builder->getResult ();
		
		return $database->getId ();
	}
	
	/**
	 * Deletes the given no-cache page
	 *
	 * @param int $i_id
	 *        	The page ID
	 */
	public function deleteNoCache($i_id) {
		\core\Memory::type ( 'int', $i_id );
		
		$this->builder->delete ( 'no_cache' )->getWhere ()->addAnd ( 'id', 'i', $i_id );
		$this->builder->getResult ();
	}
}