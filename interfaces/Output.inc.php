<?php 
interface Output {
	/**
	 * Loads the given view into the parser
	 *
	 * @param string $s_view
	 *            The view relative to the template-directory
	 * @throws TemplateException if the view does not exist
	 * @throws IOException if the view is not readable
	 */
	public function loadView($s_view = '');
	
	/**
	 * Writes a script link to the head
	 *
	 * @param string $s_link
	 *            The link
	 */
	public function setJavascriptLink($s_link);
	
	/**
	 * Writes javascript code to the head
	 *
	 * @param string $s_javascript
	 *            The code
	 */
	public function setJavascript($s_javascript);
	
	/**
	 * Writes a stylesheet link to the head
	 *
	 * @param string $s_link
	 *            The link
	 */
	public function setCssLink($s_link);
	
	/**
	 * Writes CSS code to the head
	 *
	 * @param string $s_css
	 *            The code
	 */
	public function setCSS($s_css);
	
	/**
	 * Writes a metatag to the head
	 *
	 * @param string $s_meta
	 *            The metatag
	 */
	public function setMetaLink($s_meta);
	
	/**
	 * Loads a subtemplate into the template
	 *
	 * @param string $s_key
	 *            The key in the template
	 * @param string $s_url
	 *            The URI of the subtemplate
	 * @throws TemplateException if the view does not exist
	 * @throws IOException if the view is not readable
	 */
	public function loadTemplate($s_key, $s_url);
	
	/**
	 * Sets a subtemplate into the template
	 *
	 * @param string $s_key
	 *            The key in the template
	 * @param string $s_template
	 *            The template to add
	 */
	public function setTemplate($s_key, $s_template);
	
	/**
	 * Loads a template and returns it as a string
	 *
	 * @param string $s_url
	 *            The URI of the template
	 * @param string $s_dir
	 *            to search from, optional
	 * @return string template
	 * @throws TemplateException if the view does not exist
	 * @throws IOException if the view is not readable
	 */
	public function loadTemplateAsString($s_url, $s_dir = '');
	
	/**
	 * Sets the given value in the template on the given key
	 *
	 * @param string $s_key
	 *            The key in template
	 * @param string/CoreHtmlItem $s_value
	 *            The value to write in the template
	 * @throws TemplateException if no template is loaded yet
	 * @throws Exception if $s_value is not a string and not a subclass of CoreHtmlItem
	 */
	public function set($s_key, $s_value);
	
	/**
	 * Writes a repeating block to the template
	 *
	 * @param string $s_key
	 *            The key in template
	 * @param array $a_data
	 *            block data
	 */
	public function setBlock($s_key, $a_data);
	
	/**
	 * Displays the if part with the given key
	 *
	 * @param string $s_key
	 *            The key in template
	 */
	public function displayPart($s_key);
	
	/**
	 * Writes the values to the given keys on the given template
	 *
	 * @param array $a_keys
	 * @param array $a_values
	 * @param string $s_template
	 *            The template to parse
	 * @return string parsed template
	 */
	public function writeTemplate($a_keys, $a_values, $s_template);
	
	/**
	 * Prints the page to the screen and pushes it to the visitor
	 */
	public function printToScreen();
}
?>