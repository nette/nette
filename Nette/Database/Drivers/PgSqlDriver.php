<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Database\Drivers;

use Nette;



/**
 * Supplemental PostgreSQL database driver.
 *
 * @author     David Grudl
 */
class PgSqlDriver extends Nette\Object implements Nette\Database\ISupplementalDriver
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
		throw new Nette\NotImplementedException;
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



	/**
	 * Normalizes result row.
	 */
	public function normalizeRow($row, $statement)
	{
		return $row;
	}



	/********************* reflection ****************d*g**/



	/**
	 * Returns list of tables.
	 */
	public function getTables()
	{
		return $this->connection->query("
			SELECT table_name as name, CAST(table_type = 'VIEW' AS INTEGER) as view
			FROM information_schema.tables
			WHERE table_schema = current_schema()
		")->fetchAll();
	}



	/**
	 * Returns metadata for all columns in a table.
	 */
	public function getColumns($table)
	{
		$primary = (int) $this->connection->query("
			SELECT indkey
			FROM pg_class
			LEFT JOIN pg_index on pg_class.oid = pg_index.indrelid AND pg_index.indisprimary
			WHERE pg_class.relname = {$this->connection->quote($table)}
		")->fetchColumn(0);

		$columns = array();
		foreach ($this->connection->query("
			SELECT *
			FROM information_schema.columns
			WHERE table_name = {$this->connection->quote($table)} AND table_schema = current_schema()
			ORDER BY ordinal_position
		") as $row) {
			$size = (int) max($row['character_maximum_length'], $row['numeric_precision']);
			$columns[] = array(
				'name' => $row['column_name'],
				'table' => $table,
				'nativetype' => strtoupper($row['udt_name']),
				'size' => $size ? $size : NULL,
				'nullable' => $row['is_nullable'] === 'YES',
				'default' => $row['column_default'],
				'autoincrement' => (int) $row['ordinal_position'] === $primary && substr($row['column_default'], 0, 7) === 'nextval',
				'primary' => (int) $row['ordinal_position'] === $primary,
				'vendor' => (array) $row,
			);
		}
		return $columns;
	}



	/**
	 * Returns metadata for all indexes in a table.
	 */
	public function getIndexes($table)
	{
		$columns = array();
		foreach ($this->connection->query("
			SELECT ordinal_position, column_name
			FROM information_schema.columns
			WHERE table_name = {$this->connection->quote($table)} AND table_schema = current_schema()
			ORDER BY ordinal_position
		") as $row) {
			$columns[$row['ordinal_position']] = $row['column_name'];
		}

		$indexes = array();
		foreach ($this->connection->query("
			SELECT pg_class2.relname, indisunique, indisprimary, indkey
			FROM pg_class
			LEFT JOIN pg_index on pg_class.oid = pg_index.indrelid
			INNER JOIN pg_class as pg_class2 on pg_class2.oid = pg_index.indexrelid
			WHERE pg_class.relname = {$this->connection->quote($table)}
		") as $row) {
			$indexes[$row['relname']]['name'] = $row['relname'];
			$indexes[$row['relname']]['unique'] = $row['indisunique'] === 't';
			$indexes[$row['relname']]['primary'] = $row['indisprimary'] === 't';
			foreach (explode(' ', $row['indkey']) as $index) {
				$indexes[$row['relname']]['columns'][] = $columns[$index];
			}
		}
		return array_values($indexes);
	}



	/**
	 * Returns metadata for all foreign keys in a table.
	 */
	public function getForeignKeys($table)
	{
		throw new NotImplementedException;
	}



	/**
	 * @return bool
	 */
	public function isSupported($item)
	{
		return $item === self::META;
	}

}
