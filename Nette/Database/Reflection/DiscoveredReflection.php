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
 * Reflection metadata class with discovery for a database.
 *
 * @author     Jakuv Vrana
 * @property-write Nette\Database\Connection $connection
 */
class DiscoveredReflection extends Nette\Object implements Nette\Database\IReflection
{
	/** @var Nette\Caching\Cache */
	protected $cache;

	/** @var Nette\Caching\IStorage */
	protected $cacheStorage;

	/** @var Nette\Database\Connection */
	protected $connection;

	/** @var string */
	protected $foreign;

	/** @var array */
	protected $structure = array();



	/**
	 * Create autodiscovery structure.
	 * @param  Nette\Caching\IStorage
	 * @param  string use "%s_id" to access $name . "_id" column in $row->$name
	 */
	public function __construct(Nette\Caching\IStorage $storage = NULL, $foreign = '%s_id')
	{
		$this->cacheStorage = $storage;
		$this->foreign = (string) $foreign;
	}



	public function setConnection(Nette\Database\Connection $connection)
	{
		$this->connection = $connection;

		if ($this->cacheStorage) {
			$this->cache = new Nette\Caching\Cache($this->cacheStorage, 'Nette.Database.Discovery/' . $connection->getDsn());
			$this->structure = $this->cache->load('structure');
		}
	}



	public function __destruct()
	{
		if ($this->cache) {
			$this->cache->save('structure', $this->structure);
		}
	}



	public function getPrimary($table)
	{
		$primary = & $this->structure['primary'][$table];
		if (isset($primary)) {
			return $primary;
		}

		if ($this->connection->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'sqlite') {
			$query = $this->connection->query("PRAGMA table_info($table)");
			$primaryKey = 'pk';
			$primaryVal = '1';
			$primaryKeyColumn = 'name';
		} else {
			$query = $this->connection->query("EXPLAIN $table");
			$primaryKey = 3;
			$primaryVal = 'PRI';
			$primaryKeyColumn = 0;
		}

		foreach ($query as $column) {
			if ($column[$primaryKey] === $primaryVal) { // 3 - "Key" is not compatible with PDO::CASE_LOWER
				if ($primary !== NULL) {
					$primary = FALSE; // multi-column primary key is not supported
					break;
				}
				$primary = $column[$primaryKeyColumn];
			}
		}

		return $primary;
	}



	public function getReferencingColumn($name, $table)
	{
		$name = strtolower($name);
		$columns = & $this->structure['referencing'][$table];
		if (isset($columns[$name])) {
			return $columns[$name];
		}

		if ($this->connection->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'sqlite') {
			foreach ($this->connection->query("PRAGMA foreign_key_list($name)") as $row) {
				if ($row[2] === $table && $row[4] === $this->getPrimary($table)) {
					$columns[$name] = $row[3];
				}
			}
		} else {
			foreach ($this->connection->query('
				SELECT TABLE_NAME, COLUMN_NAME
				FROM information_schema.KEY_COLUMN_USAGE
				WHERE TABLE_SCHEMA = DATABASE()
				AND REFERENCED_TABLE_SCHEMA = DATABASE()
				AND REFERENCED_TABLE_NAME = ' . $this->connection->quote($table) . '
				AND REFERENCED_COLUMN_NAME = ' . $this->connection->quote($this->getPrimary($table)) //! may not reference primary key
			) as $row) {
				$columns[strtolower($row[0])] = $row[1];
			}
		}

		return $columns[$name];
	}



	public function getReferencedColumn($name, $table)
	{
		return sprintf($this->foreign, $name);
	}



	public function getReferencedTable($name, $table)
	{
		$column = strtolower($this->getReferencedColumn($name, $table));
		$tables = & $this->structure['referenced'][$table];
		if (isset($tables[$column])) {
			return $tables[$column];
		}

		if ($this->connection->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'sqlite') {
			foreach ($this->connection->query("PRAGMA foreign_key_list($table)") as $row) {
				$tables[strtolower($row[3])] = $row[2];
			}
		} else {
			foreach ($this->connection->query('
				SELECT COLUMN_NAME, REFERENCED_TABLE_NAME
				FROM information_schema.KEY_COLUMN_USAGE
				WHERE TABLE_SCHEMA = DATABASE()
				AND REFERENCED_TABLE_SCHEMA = DATABASE()
				AND TABLE_NAME = ' . $this->connection->quote($table)
			) as $row) {
				$tables[strtolower($row[0])] = $row[1];
			}
		}

		return $tables[$column];
	}

}
