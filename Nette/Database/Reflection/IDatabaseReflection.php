<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Database\Reflection;

use Nette;



/**
 * Information about tables and columns structure
 */
interface IDatabaseReflection
{
	const FIELD_TEXT = 'string',
		FIELD_BINARY = 'bin',
		FIELD_BOOL = 'bool',
		FIELD_INTEGER = 'int',
		FIELD_FLOAT = 'float',
		FIELD_DATETIME = 'datetime';

	/**
	 * Get primary key of a table in $db->table($table)
	 * @param string
	 * @return string
	 */
	function getPrimary($table);

	/**
	 * Get column holding foreign key in $table[$id]->$name()
	 * @param string
	 * @param string
	 * @return string
	 */
	function getReferencingColumn($name, $table);

	/**
	 * Get column holding foreign key in $table[$id]->$name
	 * @param string
	 * @param string
	 * @return string
	 */
	function getReferencedColumn($name, $table);

	/**
	 * Get table holding foreign key in $table[$id]->$name
	 * @param string
	 * @param string
	 * @return string
	 */
	function getReferencedTable($name, $table);

}
