<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Database\Table;

use Nette,
	Nette\Caching\Cache,
	Nette\Caching\IStorage,
	Nette\Database\Connection,
	Nette\Database\IReflection,
	Nette\Database\ISupplementalDriver;



/**
 * Table row result set.
 * ResultSet is based on the great library NotORM http://www.notorm.com written by Jakub Vrana.
 *
 * @author     Jan Skrasek
 *
 * @property-read string $sql
 */
abstract class BaseResultSet extends Nette\Object implements \Iterator, IRowContainer, \Countable
{
	/** @var Connection */
	protected $connection;

	/** @var IReflection */
	protected $reflection;

	/** @var Cache */
	protected $cache;

	/** @var string table name */
	protected $name;

	/** @var string primary key field name */
	protected $primary;

	/** @var string|bool primary column sequence name, FALSE for autodetection */
	protected $primarySequence = FALSE;

	/** @var IRow[] data read from database in [primary key => IRow] format */
	protected $rows;

	/** @var IRow[] modifiable data in [primary key => IRow] format */
	protected $data;

	/** @var mixed */
	protected $refCache;

	/** @var mixed cache array of Selection and GroupedSelection prototypes */
	protected $globalRefCache;

	/** @var array of primary key values */
	protected $keys = array();



	/**
	 * Creates table row result set.
	 * @param  string
	 * @param  Connection
	 * @param  IReflection
	 * @param  IStorage
	 */
	public function __construct($table, Connection $connection, IReflection $reflection, IStorage $cacheStorage = NULL)
	{
		$this->name = $table;
		$this->connection = $connection;
		$this->reflection = $reflection;
		$this->cache = $cacheStorage ? new Nette\Caching\Cache($cacheStorage, 'Nette.Database.' . md5($connection->getDsn())) : NULL;
		$this->primary = $reflection->getPrimary($table);
	}



	/**
	 * @return Connection
	 */
	public function getConnection()
	{
		return $this->connection;
	}



	/**
	 * @return IReflection
	 */
	public function getDatabaseReflection()
	{
		return $this->reflection;
	}



	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}



	/**
	 * @return string|array
	 */
	public function getPrimary()
	{
		if ($this->primary === NULL) {
			throw new \LogicException("Table \"{$this->name}\" does not have a primary key.");
		}
		return $this->primary;
	}



	/**
	 * @return string
	 */
	public function getPrimarySequence()
	{
		if ($this->primarySequence === FALSE) {
			$this->primarySequence = NULL;

			$primary = $this->getPrimary();
			$driver = $this->connection->getSupplementalDriver();
			if ($driver->isSupported(ISupplementalDriver::SUPPORT_SEQUENCE)) {
				foreach ($driver->getColumns($this->name) as $column) {
					if ($column['name'] === $primary) {
						$this->primarySequence = $column['vendor']['sequence'];
						break;
					}
				}
			}
		}

		return $this->primarySequence;
	}



	/**
	 * @param  string
	 * @return Selection provides a fluent interface
	 */
	public function setPrimarySequence($sequence)
	{
		$this->primarySequence = $sequence;
		return $this;
	}



	/**
	 * @return string
	 */
	abstract public function getSql();




	/********************* quick access ****************d*g**/



	/**
	 * @inheritDoc
	 */
	public function fetch()
	{
		$this->execute();
		$return = current($this->data);
		next($this->data);
		return $return;
	}



	/**
	 * @inheritDoc
	 */
	public function fetchPairs($key, $value = NULL)
	{
		$return = array();
		foreach ($this as $row) {
			$return[is_object($row[$key]) ? (string) $row[$key] : $row[$key]] = ($value === NULL ? $row : $row[$value]);
		}
		return $return;
	}



	/**
	 * @inheritDoc
	 */
	public function fetchAll()
	{
		return iterator_to_array($this);
	}



	/**
	 * Counts number of rows.
	 * @return int
	 */
	public function count()
	{
		$this->execute();
		return count($this->data);
	}



	/********************* internal ****************d*g**/



	abstract protected function execute();



	public function createSelectionInstance($table = NULL)
	{
		return new Selection($this->connection, $table ?: $this->name, $this->reflection, $this->cache ? $this->cache->getStorage() : NULL);
	}



	protected function createGroupedSelectionInstance($table, $column)
	{
		return new GroupedSelection($table, $column, $this, $this->connection, $this->reflection, $this->cache ? $this->cache->getStorage() : NULL);
	}



	abstract protected function getSpecificCacheKey();



	/********************* references ****************d*g**/



	/**
	 * Returns referenced row.
	 * @param  string
	 * @param  string
	 * @param  mixed   primary key to check for $table and $column references
	 * @return Selection or array() if the row does not exist
	 */
	public function getReferencedTable($table, $column, $checkPrimaryKey)
	{
		$referenced = & $this->refCache['referenced'][$this->getSpecificCacheKey()]["$table.$column"];
		$selection = & $referenced['selection'];
		$cacheKeys = & $referenced['cacheKeys'];
		if ($selection === NULL || !isset($cacheKeys[$checkPrimaryKey])) {
			$this->execute();
			$cacheKeys = array();
			foreach ($this->rows as $row) {
				if ($row[$column] === NULL) {
					continue;
				}

				$key = $row[$column];
				$cacheKeys[$key] = TRUE;
			}

			if ($cacheKeys) {
				$selection = $this->createSelectionInstance($table);
				$selection->where($selection->getPrimary(), array_keys($cacheKeys));
			} else {
				$selection = array();
			}
		}

		return $selection;
	}



	/**
	 * Returns referencing rows.
	 * @param  string
	 * @param  string
	 * @param  int primary key
	 * @return GroupedSelection
	 */
	public function getReferencingTable($table, $column, $active = NULL)
	{
		$prototype = & $this->refCache['referencingPrototype']["$table.$column"];
		if (!$prototype) {
			$prototype = $this->createGroupedSelectionInstance($table, $column);
			$prototype->where("$table.$column", array_keys((array) $this->rows));
		}

		$clone = clone $prototype;
		$clone->setActive($active);
		return $clone;
	}



	/********************* interface Iterator ****************d*g**/



	public function rewind()
	{
		$this->execute();
		$this->keys = array_keys($this->data);
		reset($this->keys);
	}



	/** @return IRow */
	public function current()
	{
		if (($key = current($this->keys)) !== FALSE) {
			return $this->data[$key];
		} else {
			return FALSE;
		}
	}



	/**
	 * @return string row ID
	 */
	public function key()
	{
		return current($this->keys);
	}



	public function next()
	{
		next($this->keys);
	}



	public function valid()
	{
		return current($this->keys) !== FALSE;
	}

}
