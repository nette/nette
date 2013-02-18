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
 * @author     Jan Skrasek
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
	protected $structure = array();

	/** @var array */
	protected $loadedStructure;



	/**
	 * Create autodiscovery structure.
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
			$this->structure = $this->loadedStructure = $this->cache->load('structure') ?: $this->structure;
		}
	}



	public function __destruct()
	{
		if ($this->cache && $this->structure !== $this->loadedStructure) {
			$this->cache->save('structure', $this->structure);
		}
	}



	public function getPrimary($table)
	{
		$primary = & $this->structure['primary'][strtolower($table)];
		if (isset($primary)) {
			return empty($primary) ? NULL : $primary;
		}

		$columns = $this->connection->getSupplementalDriver()->getColumns($table);
		$primary = array();
		foreach ($columns as $column) {
			if ($column['primary']) {
				$primary[] = $column['name'];
			}
		}

		if (count($primary) === 0) {
			return NULL;
		} elseif (count($primary) === 1) {
			$primary = reset($primary);
		}

		return $primary;
	}



	public function getHasManyReference($table, $key, $refresh = TRUE)
	{
		if (isset($this->structure['hasMany'][strtolower($table)])) {
			$candidates = $columnCandidates = array();
			foreach ($this->structure['hasMany'][strtolower($table)] as $targetPair) {
				list($targetColumn, $targetTable) = $targetPair;
				if (stripos($targetTable, $key) === FALSE)
					continue;

				$candidates[] = array($targetTable, $targetColumn);
				if (stripos($targetColumn, $table) !== FALSE) {
					$columnCandidates[] = $candidate = array($targetTable, $targetColumn);
					if (strtolower($targetTable) === strtolower($key))
						return $candidate;
				}
			}

			if (count($columnCandidates) === 1) {
				return reset($columnCandidates);
			} elseif (count($candidates) === 1) {
				return reset($candidates);
			}

			foreach ($candidates as $candidate) {
				list($targetTable, $targetColumn) = $candidate;
				if (strtolower($targetTable) === strtolower($key))
					return $candidate;
			}
		}

		if ($refresh) {
			$this->reloadAllForeignKeys();
			return $this->getHasManyReference($table, $key, FALSE);
		}

		if (empty($candidates)) {
			throw new MissingReferenceException("No reference found for \${$table}->related({$key}).");
		} else {
			throw new AmbiguousReferenceKeyException('Ambiguous joining column in related call.');
		}
	}



	public function getBelongsToReference($table, $key, $refresh = TRUE)
	{
		if (isset($this->structure['belongsTo'][strtolower($table)])) {
			foreach ($this->structure['belongsTo'][strtolower($table)] as $column => $targetTable) {
				if (stripos($column, $key) !== FALSE) {
					return array($targetTable, $column);
				}
			}
		}

		if ($refresh) {
			$this->reloadForeignKeys($table);
			return $this->getBelongsToReference($table, $key, FALSE);
		}

		throw new MissingReferenceException("No reference found for \${$table}->{$key}.");
	}



	protected function reloadAllForeignKeys()
	{
		foreach ($this->connection->getSupplementalDriver()->getTables() as $table) {
			if ($table['view'] == FALSE) {
				$this->reloadForeignKeys($table['name']);
			}
		}

		foreach ($this->structure['hasMany'] as & $table) {
			uksort($table, function($a, $b) {
				return strlen($a) - strlen($b);
			});
		}
	}



	protected function reloadForeignKeys($table)
	{
		foreach ($this->connection->getSupplementalDriver()->getForeignKeys($table) as $row) {
			$this->structure['belongsTo'][strtolower($table)][$row['local']] = $row['table'];
			$this->structure['hasMany'][strtolower($row['table'])][$row['local'] . $table] = array($row['local'], $table);
		}

		if (isset($this->structure['belongsTo'][$table])) {
			uksort($this->structure['belongsTo'][$table], function($a, $b) {
				return strlen($a) - strlen($b);
			});
		}
	}

}
