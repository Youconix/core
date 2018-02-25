<?php

interface OutputInterface
{

    /**
     * Loads the given view into the parser
     *
     * @param string $s_view
     *            The view relative to the template-directory
     * @param string $s_templateDir
     *		  Override the default template directory
     * @throws \TemplateException if the view does not exist
     * @throws \IOException if the view is not readable
     */
    public function load($s_view,$s_templateDir = '');

    /**
     * Sets the given value in the template on the given key
     *
     * @param string $s_key
     *            The key in template
     * @param string/CoreHtmlItem $s_value
     *            The value to write in the template
     * @throws \TemplateException if no template is loaded yet
     * @throws \Exception if $s_value is not a string and not a subclass of CoreHtmlItem
     */
    public function set($s_key, $s_value);
    
    /**
     * Appends the given value in the template on the given key
     *
     * @param string $s_key
     *            The key in template
     * @param string/CoreHtmlItem $s_value
     *            The value to write in the template
     * @param bool  Set to true to replace the value
     * @throws \TemplateException if no template is loaded yet
     * @throws \Exception if $s_value is not a string and not a subclass of CoreHtmlItem
     */
    public function append($s_key, $s_value,$bo_override = false);
    
    /**
     * Sets an array if key-value pairs
     * 
     * @param array $a_data
     * @throws \TemplateException if no template is loaded yet
     * @throws \Exception if $s_value is not a string and not a subclass of CoreHtmlItem
     */
    public function setArray($a_data);

    /**
     * Prints the page to the screen and pushes it to the visitor
     */
    public function printToScreen();
}
?>