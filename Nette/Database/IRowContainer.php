<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Database;


/**
 * Container of database result fetched into IRow.
 *
 * @author     Jan Skrasek
 */
interface IRowContainer extends \Traversable
{

	/**
	 * Fetches single row object.
	 * @return IRow|bool if there is no row
	 */
	function fetch();

	/**
	 * Fetches all rows as associative array.
	 * @param  string column name used for an array key or NULL for numeric index
	 * @param  string column name used for an array value or NULL for the whole row
	 * @return array
	 */
	function fetchPairs($key = NULL, $value = NULL);

	/**
	 * Fetches all rows.
	 * @return IRow[]
	 */
	function fetchAll();

}
