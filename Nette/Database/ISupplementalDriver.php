<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 */

namespace Nette\Database;

use Nette;



/**
 * Supplemental PDO database driver.
 *
 * @author     David Grudl
 */
interface ISupplementalDriver
{

	/**
	 * Delimites identifier for use in a SQL statement.
	 * @param  string
	 * @return string
	 */
	function delimite($name);

	/**
	 * Formats date-time for use in a SQL statement.
	 * @param  DateTime
	 * @return string
	 */
	function formatDateTime(\DateTime $value);

	/**
	 * Encodes string for use in a LIKE statement.
	 * @param  string
	 * @param  int
	 * @return string
	 */
	function formatLike($value, $pos);

	/**
	 * Injects LIMIT/OFFSET to the SQL query.
	 * @param  string  SQL query that will be modified.
	 * @param  int
	 * @param  int
	 * @return void
	 */
	function applyLimit(&$sql, $limit, $offset);

	/**
	 * Normalizes result row.
	 * @param  array
	 * @param  Statement
	 * @return array
	 */
	function normalizeRow($row, $statement);

}
