<?php
namespace youconix\core\helpers\html;

class TableFactory
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
	 * Creates a new table
	 * 
	 * @return \youconix\core\helpers\html\Table
	 */
	public function table()
	{
		return new \youconix\core\helpers\html\Table();
	}

	/**
	 * Craetes a new table row
	 * 
	 * @return \youconix\core\helpers\html\TableRow
	 */
	public function row()
	{
		return new \youconix\core\helpers\html\TableRow();
	}

	/**
	 * Creates a new table cell
	 * 
	 * @param string $s_content
	 * @return \youconix\core\helpers\html\TableCell
	 */
	public function cell($s_content)
	{
		return new \youconix\core\helpers\html\TableCell($s_content);
	}
}