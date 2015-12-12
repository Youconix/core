<?php

namespace core\templating;

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
 * Template parser for joining the templates and the PHP code
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @version 1.0
 * @since 1.0
 */
class Template extends \core\services\Service implements \Output, \SplObserver {
	
	/**
	 *
	 * @var \Headers
	 */
	private $headers;
	
	/**
	 *
	 * @var \Cache
	 */
	private $cache;
	
	/**
	 *
	 * @var \Config
	 */
	private $config;
	
	/**
	 *
	 * @var \core\services\File
	 */
	private $file;
	private $s_layout;
	private $s_template;
	private $s_templateDir;
	private $a_blocks;
	private $a_parser;
	private $a_headerParser;
	private $a_parts;
	private $bo_compression = false;
	private $a_partsUsed = array ();
	private $bo_loaded = false;
	
	/**
	 * PHP 5 constructor
	 *
	 * @param \core\services\FileHandler $file
	 *        	The File service
	 * @param \Config $config
	 *        	The configuration model
	 * @param \Cache $cache
	 *        	The caching service
	 * @param \Headers $headers
	 *        	The headers service
	 * @throws \TemplateException if the layout does not exist
	 * @throws \IOException if the layout is not readable
	 */
	public function __construct(\core\services\FileHandler $file, \Config $config, \Cache $cache, \Headers $headers) {
		$this->file = $file;
		$this->config = $config;
		$this->headers = $headers;
		$this->cache = $cache;
		
		$this->config->attach ( $this );
		
		$this->load ();
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
	 * React on config change
	 */
	public function update(\SplSubject $subject) {
		$this->load ();
	}
	
	/**
	 * (re)loads the template parser
	 *
	 * @throws \TemplateException If the layout of view could not be loaded
	 */
	private function load() {
		$this->a_blocks = array ();
		$this->a_parser = array ();
		$this->a_parts = array ();
		
		$this->a_headerParser = array (
				'css_link' => array (),
				'js_link' => array (),
				'meta' => array (),
				'css' => array (),
				'js' => array () 
		);
		
		$this->s_templateDir = $this->config->getTemplateDir ();
		
		if (defined ( 'PROCES' ) || \core\Memory::isTesting ()) {
			return;
		}
		
		$this->cache->checkCache (); // Program wil stop if cache is used
		
		/* Load layout */
		if (! $this->config->isAjax ()) {
			
			$s_url = 'styles/' . $this->s_templateDir . '/templates/layouts/' . $this->config->getLayout () . '.tpl';
			
			if (! $this->file->exists ( NIV . $s_url )) {
				throw new \TemplateException ( 'Can not load layout ' . $s_url );
			}
			
			$this->s_layout = $this->file->readFile ( NIV . $s_url );
		}
		
		$this->loadView ();
		
		$this->compression ();
		
		$this->bo_loaded = true;
	}
	
	/**
	 * Returns the template directory
	 *
	 * @deprecated Replaced by core/models/Config:getTemplateDir
	 * @return string The template directory
	 */
	public function getTemplateDir() {
		if (! \core\Memory::isTesting ()) {
			trigger_error ( "This function has been deprecated in favour of core/models/Config->getTemplateDir().", E_USER_DEPRECATED );
		}
		return $this->config->getTemplateDir ();
	}
	
	/**
	 * Returns the loaded template directory
	 *
	 * @deprecated Replaced by core/models/Config:getStylesDir;
	 * @return string template directory
	 */
	public function getStylesDir() {
		if (! \core\Memory::isTesting ()) {
			trigger_error ( "This function has been deprecated in favour of core/models/Config->getStylesDir().", E_USER_DEPRECATED );
		}
		return $this->config->getStylesDir ();
	}
	
	/**
	 * Checks if gzip compression is available and enables it
	 */
	private function compression() {
		/* Check encoding */
		if (empty ( $_SERVER ['HTTP_ACCEPT_ENCODING'] )) {
			return;
		}
		
		/* Check server wide compression */
		if ((ini_get ( 'zlib.output_compression' ) == 'On' || ini_get ( 'zlib.output_compression_level' ) > 0) || ini_get ( 'output_handler' ) == 'ob_gzhandler') {
			return;
		}
		
		if (extension_loaded ( 'zlib' ) && (stripos ( $_SERVER ['HTTP_ACCEPT_ENCODING'], 'gzip' ) !== FALSE) && ! defined ( 'DEBUG' )) {
			ob_start ( 'ob_gzhandler' );
			$this->bo_compression = true;
		}
	}
	
	/**
	 * Loads the given view into the parser
	 *
	 * @param string $s_view
	 *        	The view relative to the template-directory
	 * @throws TemplateException if the view does not exist
	 * @throws IOException if the view is not readable
	 */
	public function loadView($s_view = '') {
		\core\Memory::type ( 'string', $s_view );
		
		/* Check view */
		if (empty ( $s_view )) {
			if (! defined ( 'TEMPLATE' )) {
				$s_view = $this->config->getCommand ();
				
				$s_template = str_replace ( '.php', '', $this->config->getPage () ) . '/' . $s_view;
			} else {
				$a_template = explode ( '/', TEMPLATE );
				$s_view = end ( $a_template );
				
				$s_template = TEMPLATE;
			}
		} else {
			$s_template = str_replace ( '.php', '', $this->config->getPage () ) . '/' . $s_view;
		}
		
		if (substr ( $s_template, - 4 ) != '.tpl') {
			$s_template .= '.tpl';
		}
		
		$this->s_viewName = preg_replace ( "#/[a-z0-0_]+\.tpl#si", '', $s_view );
		$s_view = 'styles/' . $this->s_templateDir . '/templates/' . $s_template;
		
		if (! $this->file->exists ( NIV . $s_view )) {
			/* View not found */
			throw new \TemplateException ( 'Can not load view ' . $s_view . '.' );
		}
		
		$s_view = $this->file->readFile ( NIV . $s_view );
		
		if (! $this->config->isAjax ()) {
			$this->s_template = str_replace ( "{body_content}", $s_view, $this->s_layout );
		} else {
			$this->s_template = $s_view;
		}
		
		$this->parse ();
	}
	
	/**
	 * Parses the template
	 *
	 * @throws \TemplateException If the included templates could not be found
	 */
	private function parse() {
		preg_match_all ( '/\<include src="([a-zA-Z\-_\.\/]+)"\>/', $this->s_template, $a_matches );
		
		if (array_key_exists ( 1, $a_matches )) {
			$s_dir = NIV . 'styles/' . $this->s_templateDir . '/templates/';
			
			foreach ( $a_matches [1] as $s_include ) {
				if (! $this->file->exists ( $s_dir . $s_include )) {
					throw new \TemplateException ( 'Can not find included template ' . $s_dir . $s_include . '.' );
				}
				
				$s_template = $this->file->readFile ( $s_dir . $s_include );
				$this->s_template = str_replace ( '<include src="' . $s_include . '">', $s_template, $this->s_template );
			}
		}
	}
	
	/**
	 * Writes a script link to the head
	 *
	 * @param string $s_link
	 *        	The link
	 */
	public function setJavascriptLink($s_link) {
		\core\Memory::type ( 'string', $s_link );
		
		$this->a_headerParser ['js_link'] [] = $s_link;
	}
	
	/**
	 * Writes javascript code to the head
	 *
	 * @param string $s_javascript
	 *        	The code
	 */
	public function setJavascript($s_javascript) {
		\core\Memory::type ( 'string', $s_javascript );
		
		if (stripos ( $s_javascript, '<script' ) === false) {
			$s_javascript = '<script type="text/javascript">' . '<!--
        ' . $s_javascript . '
        //-->' . '</script>';
		}
		
		$this->a_headerParser ['js'] [] = trim ( $s_javascript );
	}
	
	/**
	 * Writes a stylesheet link to the head
	 *
	 * @param string $s_link
	 *        	The link
	 */
	public function setCssLink($s_link) {
		\core\Memory::type ( 'string', $s_link );
		
		$this->a_headerParser ['css_link'] [] = $s_link;
	}
	
	/**
	 * Writes CSS code to the head
	 *
	 * @param string $s_css
	 *        	The code
	 */
	public function setCSS($s_css) {
		\core\Memory::type ( 'string', $s_css );
		
		if (stripos ( $s_css, '<style' ) === false) {
			$s_css = '<style type="text/css">' . '<!--
        ' . $s_css . '
        //-->' . '</style>';
		}
		
		$this->a_headerParser ['css'] [] = $s_css;
	}
	
	/**
	 * Writes a metatag to the head
	 *
	 * @param string $s_meta
	 *        	The metatag
	 */
	public function setMetaLink($s_meta) {
		\core\Memory::type ( 'string', $s_meta );
		
		$this->a_headerParser ['meta'] [] = $s_meta;
	}
	
	/**
	 * Sets the link to the page-header
	 *
	 * @param string/CoreHtmlItem $s_link
	 *        	The link
	 * @throws Exception if $s_link is not a string and not a subclass of CoreHtmlItem
	 * @deprecated Use one of the next functions :
	 *             - setJavascriptLink
	 *             - setJavascript
	 *             - setCssLink
	 *             - setCSS
	 *             - setMetaLink
	 */
	public function headerLink($s_link) {
		if (! \core\Memory::isTesting ()) {
			trigger_error ( "This function has been deprecated in favour of dedicated functions within this class.", E_USER_DEPRECATED );
		}
		if (is_object ( $s_link ) && is_subclass_of ( $s_link, 'CoreHtmlItem' )) {
			$s_link = $s_link->generateItem ();
		} else if (is_object ( $s_link )) {
			throw new \Exception ( "Only types of CoreHTMLItem or strings can be added." );
		}
		
		if (strpos ( $s_link, '<link rel' ) !== false) {
			$this->setCssLink ( $s_link );
		} else if (stripos ( $s_link, '<script' ) !== false) {
			if (stripos ( $s_link, 'src=' ) !== false) {
				$this->setJavascript ( $s_link );
			} else {
				$this->setJavascript ( $s_link );
			}
		} else if (stripos ( $s_link, '<meta' ) !== false) {
			$this->setMetaLink ( $s_link );
		} else if (stripos ( $s_link, '<style' ) !== false) {
			$this->setCSS ( $s_link );
		}
	}
	
	/**
	 * Loads a subtemplate into the template
	 *
	 * @param string $s_key
	 *        	The key in the template
	 * @param string $s_url
	 *        	The URI of the subtemplate
	 * @throws TemplateException if the view does not exist
	 * @throws IOException if the view is not readable
	 */
	public function loadTemplate($s_key, $s_url) {
		\core\Memory::type ( 'string', $s_key );
		\core\Memory::type ( 'string', $s_url );
		
		if (substr ( $s_url, - 4 ) != '.tpl') {
			$s_url .= '.tpl';
		}
		
		if (! $this->file->exists ( NIV . 'styles/' . $this->s_templateDir . '/templates/' . $s_url )) {
			throw new \TemplateException ( 'Can not find template ' . $s_url );
		}
		
		$s_subTemplate = $this->file->readFile ( NIV . 'styles/' . $this->s_templateDir . '/templates/' . $s_url );
		
		$this->s_template = str_replace ( '{[' . $s_key . ']}', $s_subTemplate, $this->s_template );
	}
	
	/**
	 * Sets a subtemplate into the template
	 *
	 * @param string $s_key
	 *        	The key in the template
	 * @param string $s_template
	 *        	The template to add
	 */
	public function setTemplate($s_key, $s_template) {
		\core\Memory::type ( 'string', $s_key );
		\core\Memory::type ( 'string', $s_template );
		
		$this->s_template = str_replace ( '{[' . $s_key . ']}', $s_template, $this->s_template );
	}
	
	/**
	 * Loads a template and returns it as a string
	 *
	 * @param string $s_url
	 *        	The URI of the template
	 * @param string $s_dir
	 *        	to search from, optional
	 * @return string template
	 * @throws TemplateException if the view does not exist
	 * @throws IOException if the view is not readable
	 */
	public function loadTemplateAsString($s_url, $s_dir = '') {
		\core\Memory::type ( 'string', $s_url );
		\core\Memory::type ( 'string', $s_dir );
		
		if (substr ( $s_url, - 4 ) != '.tpl') {
			$s_url .= '.tpl';
		}
		
		if (empty ( $s_dir )) {
			$s_dir = str_replace ( '.php', '', \core\Memory::getPage () );
		}
		
		if (! $this->file->exists ( NIV . 'styles/' . $this->s_templateDir . '/templates/' . $s_dir . '/' . $s_url )) {
			throw new \TemplateException ( 'Can not load template templates/' . $this->s_templateDir . '/' . $s_dir . '/' . $s_url . '.' );
		}
		
		$s_subTemplate = $this->file->readFile ( NIV . 'styles/' . $this->s_templateDir . '/templates/' . $s_dir . '/' . $s_url );
		
		return $s_subTemplate;
	}
	
	/**
	 * Sets the given value in the template on the given key
	 *
	 * @param string $s_key
	 *        	The key in template
	 * @param string/CoreHtmlItem $s_value
	 *        	The value to write in the template
	 * @throws TemplateException if no template is loaded yet
	 * @throws Exception if $s_value is not a string and not a subclass of CoreHtmlItem
	 */
	public function set($s_key, $s_value) {
		\core\Memory::type ( 'string', $s_key );
		
		if (is_null ( $this->s_template )) {
			throw new \TemplateException ( 'No template is loaded for ' . $_SERVER ['PHP_SELF'] . '.' );
		}
		
		if (! preg_match ( '#^[a-zA-Z]+#', $s_key )) {
			throw new \TemplateException ( 'Template keys must start with a letter.' );
		}
		
		if (is_object ( $s_value )) {
			if (($s_value instanceof \core\helpers\Display)) {
				$s_value = $s_value->generate ();
			} else if (is_subclass_of ( $s_value, 'CoreHtmlItem' )) {
				$s_value = $s_value->generateItem ();
			} else {
				throw new \Exception ( "Only types of CoreHTMLItem or strings can be added." );
			}
		}
		
		$this->a_parser [$s_key] = $s_value;
	}
	
	/**
	 * Writes a repeating block to the template
	 *
	 * @param string $s_key
	 *        	The key in template
	 * @param array $a_data
	 *        	block data
	 */
	public function setBlock($s_key, $a_data) {
		\core\Memory::type ( 'string', $s_key );
		\core\Memory::type ( 'array', $a_data );
		
		if (! preg_match ( '#^[a-zA-Z]+#', $s_key )) {
			throw new \TemplateException ( 'Template keys must start with a letter.' );
		}
		
		if (! array_key_exists ( $s_key, $this->a_blocks )) {
			$this->a_blocks [$s_key] = array ();
		}
		
		$a_keys = array_keys ( $a_data );
		$i_num = count ( $a_keys );
		for($i = 0; $i < $i_num; $i ++) {
			$a_keys [$i] = '{' . $a_keys [$i] . '}';
		}
		$this->a_blocks [$s_key] [] = array (
				'keys' => $a_keys,
				'data' => $a_data 
		);
	}
	
	/**
	 * Displays the if part with the given key
	 *
	 * @param string $s_key
	 *        	The key in template
	 */
	public function displayPart($s_key) {
		\core\Memory::type ( 'string', $s_key );
		
		if (! preg_match ( '#^[a-zA-Z]+#', $s_key )) {
			throw new \TemplateException ( 'Template keys must start with a letter.' );
		}
		
		$this->a_parts [] = $s_key;
	}
	
	/**
	 * Writes the values to the given keys on the given template
	 *
	 * @param array $a_keys        	
	 * @param array $a_values        	
	 * @param string $s_template
	 *        	The template to parse
	 * @return string parsed template
	 */
	public function writeTemplate($a_keys, $a_values, $s_template) {
		\core\Memory::type ( 'array', $a_keys );
		\core\Memory::type ( 'array', $a_values );
		\core\Memory::type ( 'string', $s_template );
		
		$i_number = count ( $a_keys );
		for($i = 0; $i < $i_number; $i ++) {
			if (substr ( $a_keys [$i], 0, 1 ) != '{' && substr ( $a_keys [$i], - 1 ) != '}') {
				$a_keys [$i] = '{' . $a_keys [$i] . '}';
			}
		}
		
		return str_replace ( $a_keys, $a_values, $s_template );
	}
	
	/**
	 * Prints the page to the screen and pushes it to the visitor
	 */
	public function printToScreen() {
		if (! $this->bo_loaded || $this->headers->skipTemplate ()) {
			return;
		}
		
		$this->set ( 'style_dir', $this->config->getStylesDir () );
		$this->set ( 'shared_style_dir', $this->config->getSharedStylesDir () );
		$this->set ( 'NIV', $this->config->getBase () );
		
		if (! $this->config->isAjax ()) {
			/* Write header-blok to template */
			$s_headblock = '';
			$a_keys = array_keys ( $this->a_headerParser );
			foreach ( $a_keys as $s_headerKey ) {
				foreach ( $this->a_headerParser [$s_headerKey] as $s_item ) {
					$s_headblock .= $s_item . '
	';
				}
			}
			
			$this->s_template = str_replace ( "{headblock}", $s_headblock, $this->s_template );
		}
		
		$this->writeIFS ();
		
		$this->writeBlocks ();
		
		/* Write blocks to the template */
		$a_keys = array_keys ( $this->a_blocks );
		foreach ( $a_keys as $s_key ) {
			$this->writeBlock ( $s_key );
		}
		
		/* Write data to template */
		$a_keys = array_keys ( $this->a_parser );
		foreach ( $a_keys as $s_key ) {
			$this->s_template = str_replace ( "{" . $s_key . "}", $this->a_parser [$s_key], $this->s_template );
		}
		
		$s_level = $this->config->getBase ();
		if (! empty ( $s_level ) && substr ( $s_level, - 1 ) != '/') {
			$s_level .= '/';
		}
		$this->s_template = str_replace ( array (
				'{STYLE_DIR}',
				'{LEVEL}' 
		), array (
				'{LEVEL}styles/' . $this->s_templateDir,
				$s_level 
		), $this->s_template );
		
		/* Delete unused template-variables */
		$this->removeBlocks ( 'block', 0 );
		
		$this->s_template = preg_replace ( "#{+[a-zA-Z]+[a-zA-Z_0-9/]+}+#si", "", $this->s_template );
		$this->s_template = preg_replace ( "#{\[+[a-zA-Z_0-9/]+\]}+#si", "", $this->s_template );
		
		echo ($this->s_template);
		
		$this->cache->writeCache ( $this->s_template, $this->headers );
		
		$this->bo_loaded = false;
	}
	
	/**
	 * Writes the blocks
	 *
	 * @throws \TemplateException If the blocks are invalid
	 */
	private function writeBlocks() {
		$i_start = preg_match_all ( '#<block#', $this->s_template, $a_matches );
		$i_end = preg_match_all ( '#</block>#', $this->s_template, $a_matches );
		
		if ($i_start > $i_end) {
			throw new \TemplateException ( "Template validation error : number of <block> is bigger than the number of </block>." );
		} else if ($i_end > $i_start) {
			throw new \TemplateException ( "Template validation error : number of </block> is bigger than the number of <block>." );
		} else if ($i_start == 0)
			return;
	}
	
	/**
	 * Writes the block with the given key
	 *
	 * @param string $s_key
	 *        	The block key
	 */
	private function writeBlock($s_key) {
		/* Get block */
		$s_search = '<block {' . $s_key . '}>';
		
		$i_pos = stripos ( $this->s_template, $s_search );
		if ($i_pos === false) {
			throw new \TemplateException ( 'Notice : Call to undefined template block ' . $s_key . '.' );
			return;
		}
		
		/* Find end */
		$i_end = stripos ( $this->s_template, '</block>', $i_pos );
		
		/* Check for between blocks */
		$i_pos2 = stripos ( $this->s_template, '<block', $i_pos + 1 );
		$i_extra = 0;
		while ( $i_pos2 !== false && ($i_pos2 < $i_end) ) {
			$i_pos2 = stripos ( $this->s_template, '<block', $i_pos2 + 1 );
			$i_extra ++;
		}
		
		for($i = $i_extra; $i > 0; $i --) {
			$i_end = stripos ( $this->s_template, '</block>', $i_end + 1 );
		}
		
		$i_start = $i_pos + strlen ( $s_search ) + 1;
		$s_blockPre = substr ( $this->s_template, $i_pos, ($i_end - $i_pos + 8) );
		$i_search = strlen ( $s_search );
		$s_block = trim ( substr ( $s_blockPre, $i_search, (strlen ( $s_blockPre ) - 8 - $i_search) ) );
		
		/* Parse block */
		$s_blockData = '';
		foreach ( $this->a_blocks [$s_key] as $a_item ) {
			$s_blockData .= str_replace ( $a_item ['keys'], $a_item ['data'], $s_block ) . '
';
		}
		
		$this->s_template = str_replace ( $s_blockPre, $s_blockData, $this->s_template );
	}
	
	/**
	 * Removes the unused blocks
	 *
	 * @param int $i_start
	 *        	The start position
	 */
	private function removeBlocks($s_key, $i_start) {
		$i_pos = strpos ( $this->s_template, '<' . $s_key . ' {', $i_start );
		while ( $i_pos !== false ) {
			/* Find end */
			$i_end = strpos ( $this->s_template, '</' . $s_key . '>', $i_pos );
			
			/* check for nested blocks */
			$i_pos2 = strpos ( $this->s_template, '<' . $s_key . ' {', $i_pos + 1 );
			if ($i_pos2 !== false && ($i_pos2 < $i_end)) {
				$this->removeBlocks ( $s_key, $i_pos2 + 1 );
				
				$i_end = strpos ( $this->s_template, '</' . $s_key . '>', $i_pos );
			}
			
			$this->s_template = substr_replace ( $this->s_template, '', $i_pos, ($i_end - $i_pos + strlen ( '</' . $s_key . '>' )) );
			$i_pos = strpos ( $this->s_template, '<' . $s_key . ' {', $i_start );
		}
	}
	
	/**
	 * Writes the if blocks
	 *
	 * @throws TemplateException If the template is invalid
	 */
	private function writeIFS() {
		$i_start = preg_match_all ( '#<if#', $this->s_template, $a_matches );
		$i_end = preg_match_all ( '#</if>#', $this->s_template, $a_matches );
		
		if ($i_start > $i_end) {
			throw new \TemplateException ( "Template validation error : number of &lt;if&gt; is bigger than the number of &lt;/if>." );
		} else if ($i_end > $i_start) {
			throw new \TemplateException ( "Template validation error : number of &lt;/if&gt; is bigger than the number of &lt;if&gt;." );
		} else if ($i_start == 0)
			return;
			
			/* Write parts to template */
		foreach ( $this->a_parts as $s_part ) {
			$i_start = strpos ( $this->s_template, '<if {' . $s_part . '}>', 0 );
			while ( $i_start !== false ) {
				$this->s_template = substr_replace ( $this->s_template, '', $i_start, strlen ( '<if {' . $s_part . '}>' ) );
				
				$i_end = $this->findIfEnd ( $i_start );
				$this->s_template = substr_replace ( $this->s_template, '', $i_end, 5 );
				
				/* Check for matching else */
				$this->removeElse ( ($i_end - 5) );
				
				$i_start = strpos ( $this->s_template, '<if {' . $s_part . '}>', $i_start );
			}
		}
		
		/* Check remaining parts for else items */
		$i_start = strpos ( $this->s_template, '<else>', 0 );
		while ( $i_start !== false ) {
			$this->s_template = substr_replace ( $this->s_template, '', $i_start, 6 );
			$i_end = $this->findIfEnd ( $i_start, 'else' );
			$this->s_template = substr_replace ( $this->s_template, '', $i_end, 7 );
			
			$i_start = strpos ( $this->s_template, '<else>', $i_start );
		}
		
		/* Remove unused if blocks */
		$i_start = strpos ( $this->s_template, '<if', 0 );
		while ( $i_start !== false ) {
			$i_end = $this->findIfEnd ( $i_start );
			$i_length = (($i_end + 5) - $i_start);
			
			$this->s_template = substr_replace ( $this->s_template, '', $i_start, $i_length );
			
			$i_start = strpos ( $this->s_template, '<if', $i_start );
		}
	}
	
	/**
	 * Removes the unused else blocks
	 *
	 * @param int $i_start
	 *        	The start position to search on
	 */
	private function removeElse($i_start) {
		$i_else = strpos ( $this->s_template, '<else>', $i_start );
		if ($i_else === false) {
			return;
		}
		
		/* find next if */
		$i_nextIf = strpos ( $this->s_template, '<if', $i_start );
		while ( ($i_nextIf !== false) && ($i_nextIf < $i_else) ) {
			$i_else = strpos ( $this->s_template, '<else>', $i_nextIf );
			if ($i_else === false) {
				return;
			}
			
			$i_nextIf = strpos ( $this->s_template, '<if', ($i_nextIf + 1) );
		}
		
		/* Remove else part */
		$i_length = ($this->findIfEnd ( $i_else, 'else' ) - $i_else + 7);
		$this->s_template = substr_replace ( $this->s_template, '', $i_else, $i_length );
	}
	
	/**
	 * Finds the matching end-tag
	 *
	 * @param int $i_start
	 *        	The position to start searching on
	 * @param string $s_key
	 *        	The key to search on, default if
	 * @return The start position if the end tag, -1 if not found
	 */
	private function findIfEnd($i_start, $s_key = 'if') {
		$i_end = strpos ( $this->s_template, '</' . $s_key . '>', $i_start );
		if ($i_end === false) {
			return - 1;
		}
		
		$i_nextIf = strpos ( $this->s_template, '<if {', ($i_start + 1) );
		while ( ($i_nextIf !== false) && ($i_end > $i_nextIf) ) {
			$i_end = strpos ( $this->s_template, '</' . $s_key . '>', ($i_end + 1) );
			$i_nextIf = strpos ( $this->s_template, '<if {', ($i_nextIf + 1) );
		}
		
		return $i_end;
	}
}