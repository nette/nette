<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Database;



/**
 * Container of database result fetched into Row objects.
 *
 * @author     Jan Skrasek
 */
interface IRowContainer extends \Iterator
{

	/**
	 * Fetchs single row object
	 * @return Row or FALSE if there is no row
	 */
	function fetch();



	/*
	 * Fetchs all rows as associative array.
	 * @param  string
	 * @param  string column name used for an array value or NULL for the whole row
	 * @return array
	 */
	function fetchPairs($key, $value = NULL);

}
