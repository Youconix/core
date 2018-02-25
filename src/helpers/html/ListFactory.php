<?php
namespace youconix\core\helpers\html;

class ListFactory
{

	/**
	 * Handle class as singleton
	 *
	 * @return bool
	 */
	public function isSingleton(){
		return true;
	}

	protected function __clone()
	{}

	/**
	 * Creates a numbered list
	 * 
	 * @return \youconix\core\helpers\html\ListCollection
	 */
    public function numberedList()
    {
        $list = new \youconix\core\helpers\html\ListCollection(true);
        return $list;
    }

    /**
     * Creates an unumbered list
     * 
     * @return \youconix\core\helpers\html\ListCollection
     */
    public function uNumberedList()
    {
        $list = new \youconix\core\helpers\html\ListCollection(false);
        return $list;
    }

    /**
     * Creates a list item
     * 
     * @param string $s_content
     * @return \youconix\core\helpers\html\ListItem
     */
    public function createItem($s_content)
    {
        return new \youconix\core\helpers\html\ListItem($s_content);
    }
}