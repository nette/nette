<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Database\Drivers;

use Nette;


/**
 * Supplemental SQLite2 database driver.
 *
 * @author     David Grudl
 */
class Sqlite2Driver extends SqliteDriver
{

	/**
	 * Encodes string for use in a LIKE statement.
	 */
	public function formatLike($value, $pos)
	{
		throw new Nette\NotSupportedException;
	}


	/**
	 * Returns metadata for all foreign keys in a table.
	 */
	public function getForeignKeys($table)
	{
		throw new Nette\NotSupportedException; // @see http://www.sqlite.org/foreignkeys.html
	}


	/**
	 * Not supported.
	 */
	public function getColumnTypes(\PDOStatement $statement)
	{
	}

}
