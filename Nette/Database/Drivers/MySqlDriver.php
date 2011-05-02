<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Database\Drivers;

use Nette;



/**
 * Supplemental MySQL database driver.
 *
 * @author     David Grudl
 */
class MySqlDriver extends Nette\Object implements Nette\Database\ISupplementalDriver
{
	/** @var Nette\Database\Connection */
	private $connection;



	/**
	 * Driver options:
	 *   - charset => character encoding to set (default is utf8)
	 *   - sqlmode => see http://dev.mysql.com/doc/refman/5.0/en/server-sql-mode.html
	 */
	public function __construct(Nette\Database\Connection $connection, array $options)
	{
		$this->connection = $connection;
		$charset = isset($options['charset']) ? $options['charset'] : 'utf8';
		if ($charset) {
			$connection->exec("SET NAMES '$charset'");
		}
		if (isset($options['sqlmode'])) {
			$connection->exec("SET sql_mode='$options[sqlmode]'");
		}
		$connection->exec("SET time_zone='" . date('P') . "'");
	}



	/********************* SQL ****************d*g**/



	/**
	 * Delimites identifier for use in a SQL statement.
	 */
	public function delimite($name)
	{
		// @see http://dev.mysql.com/doc/refman/5.0/en/identifiers.html
		return '`' . str_replace('`', '``', $name) . '`';
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
		$value = addcslashes(str_replace('\\', '\\\\', $value), "\x00\n\r\\'%_");
		return ($pos <= 0 ? "'%" : "'") . $value . ($pos >= 0 ? "%'" : "'");
	}



	/**
	 * Injects LIMIT/OFFSET to the SQL query.
	 */
	public function applyLimit(&$sql, $limit, $offset)
	{
		if ($limit >= 0 || $offset > 0) {
			// see http://dev.mysql.com/doc/refman/5.0/en/select.html
			$sql .= ' LIMIT ' . ($limit < 0 ? '18446744073709551615' : (int) $limit)
				. ($offset > 0 ? ' OFFSET ' . (int) $offset : '');
		}
	}



	/**
	 * Normalizes result row.
	 */
	public function normalizeRow($row, $statement)
	{
		return $row;
	}

}
