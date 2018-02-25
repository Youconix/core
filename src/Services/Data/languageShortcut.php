<?php

/**
 * Language string function
 * Shortcut for $service_Language->get()
 * 
 * @param string $s_path The text path
 * @return string The text
 */
function t($s_path)
{
    $service_Language = \Loader::inject('\LanguageInterface');
    return $service_Language->get($s_path);
}

/**
 * Language block function
 * Shortcut for $service_Language->getBlock()
 *
 * @param string $s_path
 *            The block path
 * @return XmlNodeList/array The block
 */
function tb($s_path)
{
    $service_Language = \Loader::inject('\LanguageInterface');
    return $service_Language->getBlock($s_path);
}
?>