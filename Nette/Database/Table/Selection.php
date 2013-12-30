<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Database\Table;

use Nette,
	Nette\Database\ISupplementalDriver;


/**
 * Filtered table representation.
 * Selection is based on the great library NotORM http://www.notorm.com written by Jakub Vrana.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 *
 * @property-read string $sql
 */
class Selection extends Nette\Object implements \Iterator, IRowContainer, \ArrayAccess, \Countable
{
	/** @var Nette\Database\Connection */
	protected $connection;

	/** @var Nette\Database\IReflection */
	protected $reflection;

	/** @var Nette\Caching\Cache */
	protected $cache;

	/** @var SqlBuilder */
	protected $sqlBuilder;

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

	/** @var bool */
	protected $dataRefreshed = FALSE;

	/** @var mixed cache array of Selection and GroupedSelection prototypes */
	protected $globalRefCache;

	/** @var mixed */
	protected $refCache;

	/** @var string */
	protected $generalCacheKey;

	/** @var string */
	protected $specificCacheKey;

	/** @var array of [conditions => [key => IRow]]; used by GroupedSelection */
	protected $aggregation = array();

	/** @var array of touched columns */
	protected $accessedColumns;

	/** @var array of earlier touched columns */
	protected $previousAccessedColumns;

	/** @var bool should instance observe accessed columns caching */
	protected $observeCache = FALSE;

	/** @var array of primary key values */
	protected $keys = array();


	/**
	 * Creates filtered table representation.
	 * @param  Nette\Database\Connection
	 * @param  string  database table name
	 */
	public function __construct(Nette\Database\Connection $connection, $table, Nette\Database\IReflection $reflection, Nette\Caching\IStorage $cacheStorage = NULL)
	{
		$this->name = $table;
		$this->connection = $connection;
		$this->reflection = $reflection;
		$this->cache = $cacheStorage ? new Nette\Caching\Cache($cacheStorage, 'Nette.Database.' . md5($connection->getDsn())) : NULL;
		$this->primary = $reflection->getPrimary($table);
		$this->sqlBuilder = new SqlBuilder($table, $connection, $reflection);
		$this->refCache = & $this->getRefTable($refPath)->globalRefCache[$refPath];
	}


	public function __destruct()
	{
		$this->saveCacheState();
	}


	public function __clone()
	{
		$this->sqlBuilder = clone $this->sqlBuilder;
	}


	/**
	 * @return Nette\Database\Connection
	 */
	public function getConnection()
	{
		return $this->connection;
	}


	/**
	 * @return Nette\Database\IReflection
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
	 * @param  bool
	 * @return string|array
	 */
	public function getPrimary($need = TRUE)
	{
		if ($this->primary === NULL && $need) {
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
			$driver = $this->connection->getSupplementalDriver();
			if ($driver->isSupported(ISupplementalDriver::SUPPORT_SEQUENCE) && $this->primary !== NULL) {
				foreach ($driver->getColumns($this->name) as $column) {
					if ($column['name'] === $this->primary) {
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
	 * @return self
	 */
	public function setPrimarySequence($sequence)
	{
		$this->primarySequence = $sequence;
		return $this;
	}


	/**
	 * @return string
	 */
	public function getSql()
	{
		return $this->sqlBuilder->buildSelectQuery($this->getPreviousAccessedColumns());
	}


	/**
	 * Loads cache of previous accessed columns and returns it.
	 * @internal
	 * @return array|false
	 */
	public function getPreviousAccessedColumns()
	{
		if ($this->cache && $this->previousAccessedColumns === NULL) {
			$this->accessedColumns = $this->previousAccessedColumns = $this->cache->load($this->getGeneralCacheKey());
			if ($this->previousAccessedColumns === NULL) {
				$this->previousAccessedColumns = array();
			}
		}

		return array_keys(array_filter((array) $this->previousAccessedColumns));
	}


	/**
	 * @internal
	 * @return SqlBuilder
	 */
	public function getSqlBuilder()
	{
		return $this->sqlBuilder;
	}


	/********************* quick access ****************d*g**/


	/**
	 * Returns row specified by primary key.
	 * @param  mixed primary key
	 * @return IRow or FALSE if there is no such row
	 */
	public function get($key)
	{
		$clone = clone $this;
		return $clone->wherePrimary($key)->fetch();
	}


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
	public function fetchPairs($key = NULL, $value = NULL)
	{
		return Nette\Database\Helpers::toPairs(iterator_to_array($this), $key, $value);
	}


	/**
	 * @inheritDoc
	 */
	public function fetchAll()
	{
		return iterator_to_array($this);
	}


	/********************* sql selectors ****************d*g**/


	/**
	 * Adds select clause, more calls appends to the end.
	 * @param  string for example "column, MD5(column) AS column_md5"
	 * @return self
	 */
	public function select($columns)
	{
		$this->emptyResultSet();
		call_user_func_array(array($this->sqlBuilder, 'addSelect'), func_get_args());
		return $this;
	}


	/**
	 * Adds condition for primary key.
	 * @param  mixed
	 * @return self
	 */
	public function wherePrimary($key)
	{
		if (is_array($this->primary) && Nette\Utils\Arrays::isList($key)) {
			if (isset($key[0]) && is_array($key[0])) {
				$this->where($this->primary, $key);
			} else {
				foreach ($this->primary as $i => $primary) {
					$this->where($this->name . '.' . $primary, $key[$i]);
				}
			}
		} elseif (is_array($key) && !Nette\Utils\Arrays::isList($key)) { // key contains column names
			$this->where($key);
		} else {
			$this->where($this->name . '.' . $this->getPrimary(), $key);
		}

		return $this;
	}


	/**
	 * Adds where condition, more calls appends with AND.
	 * @param  string condition possibly containing ?
	 * @param  mixed
	 * @param  mixed ...
	 * @return self
	 */
	public function where($condition, $parameters = array())
	{
		if (is_array($condition) && $parameters === array()) { // where(array('column1' => 1, 'column2 > ?' => 2))
			foreach ($condition as $key => $val) {
				if (is_int($key)) {
					$this->where($val); // where('full condition')
				} else {
					$this->where($key, $val); // where('column', 1)
				}
			}
			return $this;
		}

		$this->emptyResultSet();
		call_user_func_array(array($this->sqlBuilder, 'addWhere'), func_get_args());
		return $this;
	}


	/**
	 * Adds order clause, more calls appends to the end.
	 * @param  string for example 'column1, column2 DESC'
	 * @return self
	 */
	public function order($columns)
	{
		$this->emptyResultSet();
		call_user_func_array(array($this->sqlBuilder, 'addOrder'), func_get_args());
		return $this;
	}


	/**
	 * Sets limit clause, more calls rewrite old values.
	 * @param  int
	 * @param  int
	 * @return self
	 */
	public function limit($limit, $offset = NULL)
	{
		$this->emptyResultSet();
		$this->sqlBuilder->setLimit($limit, $offset);
		return $this;
	}


	/**
	 * Sets offset using page number, more calls rewrite old values.
	 * @param  int
	 * @param  int
	 * @return self
	 */
	public function page($page, $itemsPerPage, & $numOfPages = NULL)
	{
		if (func_get_args() > 2) {
			$numOfPages = (int) ceil($this->count('*') / $itemsPerPage);
		}
		return $this->limit($itemsPerPage, ($page - 1) * $itemsPerPage);
	}


	/**
	 * Sets group clause, more calls rewrite old value.
	 * @param  string
	 * @return self
	 */
	public function group($columns)
	{
		$this->emptyResultSet();
		call_user_func_array(array($this->sqlBuilder, 'setGroup'), func_get_args());
		return $this;
	}


	/**
	 * Sets having clause, more calls rewrite old value.
	 * @param  string
	 * @return self
	 */
	public function having($having)
	{
		$this->emptyResultSet();
		call_user_func_array(array($this->sqlBuilder, 'setHaving'), func_get_args());
		return $this;
	}


	/********************* aggregations ****************d*g**/


	/**
	 * Executes aggregation function.
	 * @param  string select call in "FUNCTION(column)" format
	 * @return string
	 */
	public function aggregation($function)
	{
		$selection = $this->createSelectionInstance();
		$selection->getSqlBuilder()->importConditions($this->getSqlBuilder());
		$selection->select($function);
		foreach ($selection->fetch() as $val) {
			return $val;
		}
	}


	/**
	 * Counts number of rows.
	 * @param  string  if it is not provided returns count of result rows, otherwise runs new sql counting query
	 * @return int
	 */
	public function count($column = NULL)
	{
		if (!$column) {
			$this->execute();
			return count($this->data);
		}
		return $this->aggregation("COUNT($column)");
	}


	/**
	 * Returns minimum value from a column.
	 * @param  string
	 * @return int
	 */
	public function min($column)
	{
		return $this->aggregation("MIN($column)");
	}


	/**
	 * Returns maximum value from a column.
	 * @param  string
	 * @return int
	 */
	public function max($column)
	{
		return $this->aggregation("MAX($column)");
	}


	/**
	 * Returns sum of values in a column.
	 * @param  string
	 * @return int
	 */
	public function sum($column)
	{
		return $this->aggregation("SUM($column)");
	}


	/********************* internal ****************d*g**/


	protected function execute()
	{
		if ($this->rows !== NULL) {
			return;
		}

		$this->observeCache = $this;

		if ($this->primary === NULL && $this->sqlBuilder->getSelect() === NULL) {
			throw new Nette\InvalidStateException('Table with no primary key requires an explicit select clause.');
		}

		try {
			$result = $this->query($this->getSql());

		} catch (\PDOException $exception) {
			if (!$this->sqlBuilder->getSelect() && $this->previousAccessedColumns) {
				$this->previousAccessedColumns = FALSE;
				$this->accessedColumns = array();
				$result = $this->query($this->getSql());
			} else {
				throw $exception;
			}
		}

		$this->rows = array();
		$usedPrimary = TRUE;
		foreach ($result->getPdoStatement() as $key => $row) {
			$row = $this->createRow($result->normalizeRow($row));
			$primary = $row->getSignature(FALSE);
			$usedPrimary = $usedPrimary && $primary;
			$this->rows[$primary ?: $key] = $row;
		}
		$this->data = $this->rows;

		if ($usedPrimary && $this->accessedColumns !== FALSE) {
			foreach ((array) $this->primary as $primary) {
				$this->accessedColumns[$primary] = TRUE;
			}
		}
	}


	protected function createRow(array $row)
	{
		return new ActiveRow($row, $this);
	}


	public function createSelectionInstance($table = NULL)
	{
		return new Selection($this->connection, $table ?: $this->name, $this->reflection, $this->cache ? $this->cache->getStorage() : NULL);
	}


	protected function createGroupedSelectionInstance($table, $column)
	{
		return new GroupedSelection($this, $table, $column);
	}


	protected function query($query)
	{
		return $this->connection->queryArgs($query, $this->sqlBuilder->getParameters());
	}


	protected function emptyResultSet($saveCache = TRUE)
	{
		if ($this->rows !== NULL && $saveCache) {
			$this->saveCacheState();
		}

		$this->rows = NULL;
		$this->specificCacheKey = NULL;
		$this->generalCacheKey = NULL;
		$this->refCache['referencingPrototype'] = array();
	}


	protected function saveCacheState()
	{
		if ($this->observeCache === $this && $this->cache && !$this->sqlBuilder->getSelect() && $this->accessedColumns !== $this->previousAccessedColumns) {
			$previousAccessed = (array) $this->cache->load($this->getGeneralCacheKey());
			$accessed = (array) $this->accessedColumns;
			$needSave = array_intersect_key($accessed, $previousAccessed) !== $accessed;
			if ($needSave) {
				$this->cache->save($this->getGeneralCacheKey(), $previousAccessed + $accessed);
				$this->previousAccessedColumns = NULL;
			}
		}
	}


	/**
	 * Returns Selection parent for caching.
	 * @return Selection
	 */
	protected function getRefTable(& $refPath)
	{
		return $this;
	}


	/**
	 * Loads refCache references
	 */
	protected function loadRefCache()
	{
	}


	/**
	 * Returns general cache key indenpendent on query parameters or sql limit
	 * Used e.g. for previously accessed columns caching
	 * @return string
	 */
	protected function getGeneralCacheKey()
	{
		if ($this->generalCacheKey) {
			return $this->generalCacheKey;
		}

		return $this->generalCacheKey = md5(serialize(array(__CLASS__, $this->name, $this->sqlBuilder->getConditions())));
	}


	/**
	 * Returns object specific cache key dependent on query parameters
	 * Used e.g. for reference memory caching
	 * @return string
	 */
	protected function getSpecificCacheKey()
	{
		if ($this->specificCacheKey) {
			return $this->specificCacheKey;
		}

		return $this->specificCacheKey = md5($this->getSql() . json_encode($this->sqlBuilder->getParameters()));
	}


	/**
	 * @internal
	 * @param  string|NULL column name or NULL to reload all columns
	 * @param  bool
	 */
	public function accessColumn($key, $selectColumn = TRUE)
	{
		if (!$this->cache) {
			return;
		}

		if ($key === NULL) {
			$this->accessedColumns = FALSE;
			$currentKey = key((array) $this->data);
		} elseif ($this->accessedColumns !== FALSE) {
			$this->accessedColumns[$key] = $selectColumn;
		}

		if ($selectColumn && !$this->sqlBuilder->getSelect() && $this->previousAccessedColumns && ($key === NULL || !isset($this->previousAccessedColumns[$key]))) {
			if ($this->sqlBuilder->getLimit()) {
				$generalCacheKey = $this->generalCacheKey;
				$primaries = array();
				foreach ((array) $this->rows as $row) {
					$primary = $row->getPrimary();
					$primaries[] = is_array($primary) ? array_values($primary) : $primary;
				}
			}
			$this->previousAccessedColumns = array();
			$this->emptyResultSet(FALSE);
			if ($this->sqlBuilder->getLimit()) {
				$this->sqlBuilder->setLimit(NULL, NULL);
				$this->wherePrimary($primaries);
				$this->generalCacheKey = $generalCacheKey;
			}
			$this->dataRefreshed = TRUE;

			if ($key === NULL) {
				// we need to move iterator in resultset
				$this->execute();
				while (key($this->data) !== $currentKey) {
					next($this->data);
				}
			}
		}
	}


	/**
	 * @internal
	 * @param  string
	 */
	public function removeAccessColumn($key)
	{
		if ($this->cache && is_array($this->accessedColumns)) {
			$this->accessedColumns[$key] = FALSE;
		}
	}


	/**
	 * Returns if selection requeried for more columns.
	 * @return bool
	 */
	public function getDataRefreshed()
	{
		return $this->dataRefreshed;
	}


	/********************* manipulation ****************d*g**/


	/**
	 * Inserts row in a table.
	 * @param  array|\Traversable|Selection array($column => $value)|\Traversable|Selection for INSERT ... SELECT
	 * @return IRow|int|bool Returns IRow or number of affected rows for Selection or table without primary key
	 */
	public function insert($data)
	{
		if ($data instanceof Selection) {
			$data = new Nette\Database\SqlLiteral($data->getSql(), $data->getSqlBuilder()->getParameters());

		} elseif ($data instanceof \Traversable) {
			$data = iterator_to_array($data);
		}

		$return = $this->connection->query($this->sqlBuilder->buildInsertQuery(), $data);

		if ($data instanceof Nette\Database\SqlLiteral || $this->primary === NULL) {
			return $return->getRowCount();
		}

		$primaryKey = $this->connection->getInsertId($this->getPrimarySequence());
		if (is_array($this->getPrimary())) {
			$primaryKey = array();

			foreach ((array) $this->getPrimary() as $key) {
				if (!isset($data[$key])) {
					return $data;
				}

				$primaryKey[$key] = $data[$key];
			}
			if (count($primaryKey) === 1) {
				$primaryKey = reset($primaryKey);
			}
		}

		$row = $this->createSelectionInstance()
			->select('*')
			->wherePrimary($primaryKey)
			->fetch();

		$this->loadRefCache();
		if ($this->rows !== NULL) {
			if ($signature = $row->getSignature(FALSE)) {
				$this->rows[$signature] = $row;
				$this->data[$signature] = $row;
			} else {
				$this->rows[] = $row;
				$this->data[] = $row;
			}
		}

		return $row;
	}


	/**
	 * Updates all rows in result set.
	 * Joins in UPDATE are supported only in MySQL
	 * @param  array|\Traversable ($column => $value)
	 * @return int number of affected rows
	 */
	public function update($data)
	{
		if ($data instanceof \Traversable) {
			$data = iterator_to_array($data);

		} elseif (!is_array($data)) {
			throw new Nette\InvalidArgumentException;
		}

		if (!$data) {
			return 0;
		}

		return $this->connection->queryArgs(
			$this->sqlBuilder->buildUpdateQuery(),
			array_merge(array($data), $this->sqlBuilder->getParameters())
		)->getRowCount();
	}


	/**
	 * Deletes all rows in result set.
	 * @return int number of affected rows
	 */
	public function delete()
	{
		return $this->query($this->sqlBuilder->buildDeleteQuery())->getRowCount();
	}


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


	/********************* interface ArrayAccess ****************d*g**/


	/**
	 * Mimic row.
	 * @param  string row ID
	 * @param  IRow
	 * @return NULL
	 */
	public function offsetSet($key, $value)
	{
		$this->execute();
		$this->rows[$key] = $value;
	}


	/**
	 * Returns specified row.
	 * @param  string row ID
	 * @return IRow or NULL if there is no such row
	 */
	public function offsetGet($key)
	{
		$this->execute();
		return $this->rows[$key];
	}


	/**
	 * Tests if row exists.
	 * @param  string row ID
	 * @return bool
	 */
	public function offsetExists($key)
	{
		$this->execute();
		return isset($this->rows[$key]);
	}


	/**
	 * Removes row from result set.
	 * @param  string row ID
	 * @return NULL
	 */
	public function offsetUnset($key)
	{
		$this->execute();
		unset($this->rows[$key], $this->data[$key]);
	}

}
