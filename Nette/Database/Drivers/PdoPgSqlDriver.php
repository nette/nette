<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 */

namespace Nette\Database\Drivers;

use Nette;



/**
 * Supplemental PostgreSQL database driver.
 *
 * @author     David Grudl
 */
class PdoPgSqlDriver extends Nette\Object implements Nette\Database\ISupplementalDriver
{
	/** @var Nette\Database\Connection */
	private $connection;



	public function __construct(Nette\Database\Connection $connection, array $options)
	{
		$this->connection = $connection;
	}



	/********************* SQL ****************d*g**/



	/**
	 * Delimites identifier for use in a SQL statement.
	 */
	public function delimite($name)
	{
		// @see http://www.postgresql.org/docs/8.2/static/sql-syntax-lexical.html#SQL-SYNTAX-IDENTIFIERS
		return '"' . str_replace('"', '""', $name) . '"';
	}



	/**
	 * Formats date-time for use in a SQL statement.
	 */
	public function formatDateTime(\DateTime $value)
	{
		return $value->format("'Y-m-d H:i:s'");
	}



	/**
	 * Encodes string for use in a LIKE statement.
	 */
	public function formatLike($value, $pos)
	{
		throw new NotImplementedException;
	}



	/**
	 * Injects LIMIT/OFFSET to the SQL query.
	 */
	public function applyLimit(&$sql, $limit, $offset)
	{
		if ($limit >= 0)
			$sql .= ' LIMIT ' . (int) $limit;

		if ($offset > 0)
			$sql .= ' OFFSET ' . (int) $offset;
	}

}
