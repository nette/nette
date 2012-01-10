<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
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

	/** @var array */
	protected $structure = array(
		'primary' => array(),
		'hasMany' => array(),
		'belongsTo' => array(),
	);



	/**
	 * Create autodiscovery structure.
	 * @param  Nette\Caching\IStorage
	 */
	public function __construct(Nette\Caching\IStorage $storage = NULL)
	{
		$this->cacheStorage = $storage;
	}



	public function setConnection(Nette\Database\Connection $connection)
	{
		$this->connection = $connection;
		if ($this->cacheStorage) {
			$this->cache = new Nette\Caching\Cache($this->cacheStorage, 'Nette.Database.' . md5($connection->getDsn()));
			$this->structure = $this->cache->load('structure') ?: $this->structure;
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

		$columns = $this->connection->getSupplementalDriver()->getColumns($table);
		$primaryCount = 0;
		foreach ($columns as $column) {
			if ($column['primary']) {
				$primary = $column['name'];
				$primaryCount++;
			}
		}

		if ($primaryCount !== 1)
			return NULL;

		return $primary;
	}



	public function getHasManyReference($table, $key, $refresh = TRUE)
	{
		$reference = $this->structure['hasMany'];
		if (!empty($reference[$table])) {
			foreach ($reference[$table] as $targetTable => $targetColumn) {
				if (strpos($targetTable, strtolower($key)) !== FALSE) {
					return array(
						$targetTable,
						$targetColumn,
					);
				}
			}
		}

		if (!$refresh) {
			throw new \PDOException("No reference found for \${$table}->related({$key}).");
		}

		$this->reloadAllForeignKeys();
		return $this->getHasManyReference($table, $key, FALSE);
	}



	public function getBelongsToReference($table, $key, $refresh = TRUE)
	{
		$reference = $this->structure['belongsTo'];
		if (!empty($reference[$table])) {
			foreach ($reference[$table] as $column => $targetTable) {
				if (strpos($column, strtolower($key)) !== FALSE) {
					return array(
						$targetTable,
						$column,
					);
				}
			}
		}

		if (!$refresh) {
			throw new \PDOException("No reference found for \${$table}->{$key}.");
		}

		$this->reloadForeignKeys($table);
		return $this->getBelongsToReference($table, $key, FALSE);
	}



	protected function reloadAllForeignKeys()
	{
		foreach ($this->connection->getSupplementalDriver()->getTables() as $table) {
			if ($table['view'] == FALSE) {
				$this->reloadForeignKeys($table['name']);
			}
		}

		foreach (array_keys($this->structure['hasMany']) as $table) {
			uksort($this->structure['hasMany'][$table], function($a, $b) {
				return strlen($a) - strlen($b);
			});
		}
	}



	protected function reloadForeignKeys($table)
	{
		static $reloaded = array();
		if (isset($reloaded[$table]))
			return;

		foreach ($this->connection->getSupplementalDriver()->getForeignKeys($table) as $row) {
			$this->structure['belongsTo'][$table][strtolower($row['local'])] = $row['table'];
			$this->structure['hasMany'][strtolower($row['table'])][$table] = $row['local'];
		}

		if (isset($this->structure['belongsTo'][$table])) {
			uksort($this->structure['belongsTo'][$table], function($a, $b) {
				return strlen($a) - strlen($b);
			});
		}

		$reloaded[$table] = TRUE;
	}

}
