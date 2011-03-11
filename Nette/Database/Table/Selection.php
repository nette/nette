<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Database\Table;

use Nette,
	PDO;



/**
 * Filtered table representation.
 * Selector is based on the great library NotORM http://www.notorm.com written by Jakub Vrana.
 *
 * @author     Jakub Vrana
 */
class Selection extends Nette\Object implements \Iterator, \ArrayAccess, \Countable
{
	/** @var Nette\Database\Connection */
	public $connection;

	/** @var string table name */
	public $name;

	/** @var string primary key field name */
	public $primary;

	/** @var array of [primary key => TableRow] readed from database */
	protected $rows;

	/** @var array of [primary key => TableRow] modifiable */
	protected $data;

	/** @var array of column to select */
	protected $select = array();

	/** @var array of where conditions */
	protected $where = array();

	/** @var array of where conditions for caching */
	protected $conditions = array();

	/** @var array of parameters passed to where conditions */
	protected $parameters = array();

	/** @var array or columns to order by */
	protected $order = array();

	/** @var int number of rows to fetch */
	protected $limit = NULL;

	/** @var int first row to fetch */
	protected $offset = NULL;

	/** @var string columns to grouping */
	protected $group = '';

	/** @var string grouping condition */
	protected $having = '';

	/** @var array of referenced TableSelection */
	protected $referenced = array();

	/** @var array of [sql => [column => [key => TableRow]]] used by GroupedTableSelection */
	protected $referencing = array();

	/** @var array of [sql => [key => TableRow]] used by GroupedTableSelection */
	protected $aggregation = array();

	/** @var array of touched columns */
	protected $accessed;

	/** @var array of earlier touched columns */
	protected $prevAccessed;

	/** @var array of primary key values */
	protected $keys = array();

	/** @var string */
	protected $delimitedName;

	/** @var string */
	protected $delimitedPrimary;



	/**
	 * @param  string
	 * @param
	 */
	public function __construct($table, Nette\Database\Connection $connection)
	{
		$this->name = $table;
		$this->connection = $connection;
		$this->primary = $this->getPrimary($table);
		$this->delimitedName = $connection->getSupplementalDriver()->delimite($this->name);
		$this->delimitedPrimary = $connection->getSupplementalDriver()->delimite($this->primary);
	}



	/**
	 * Saves data to cache and empty result.
	 */
	public function __destruct()
	{
		if ($this->connection->cache && !$this->select && $this->rows !== NULL) {
			$accessed = $this->accessed;
			if (is_array($accessed)) {
				$accessed = array_filter($accessed);
			}
			$this->connection->cache->save(array(__CLASS__, $this->name, $this->conditions), $accessed);
		}
		$this->rows = NULL;
	}



	/**
	 * Returns row specified by primary key.
	 * @param  mixed
	 * @return ActiveRow or NULL if there is no such row
	 */
	public function get($key)
	{
		// can also use array_pop($this->where) instead of clone to save memory
		$clone = clone $this;
		$clone->where($this->delimitedPrimary, $key);
		return $clone->fetch();
	}



	/**
	 * Adds select clause, more calls appends to the end.
	 * @param  string for example "column, MD5(column) AS column_md5"
	 * @return Selection provides a fluent interface
	 */
	public function select($columns)
	{
		$this->__destruct();
		$this->select[] = $this->tryDelimite($columns);
		return $this;
	}



	/**
	 * Selects by primary key.
	 * @param  mixed
	 * @return Selection provides a fluent interface
	 */
	public function find($key)
	{
		return $this->where($this->delimitedPrimary, $key);
	}



	/**
	 * Adds where condition, more calls appends with AND.
	 * @param  string condition possibly containing ?
	 * @param  mixed
	 * @param  mixed ...
	 * @return Selection provides a fluent interface
	 */
	public function where($condition, $parameters = array())
	{
		if (is_array($condition)) { // where(array('column1' => 1, 'column2 > ?' => 2))
			foreach ($condition as $key => $val) {
				$this->where($key, $val);
			}
			return $this;
		}

		$this->__destruct();

		$this->conditions[] = $condition = $this->tryDelimite($condition);

		$args = func_num_args();
		if ($args !== 2 || strpbrk($condition, '?:')) { // where('column < ? OR column > ?', array(1, 2))
			if ($args !== 2 || !is_array($parameters)) { // where('column < ? OR column > ?', 1, 2)
				$parameters = func_get_args();
				array_shift($parameters);
			}
			$this->parameters = array_merge($this->parameters, $parameters);

		} elseif ($parameters === NULL) { // where('column', NULL)
			$condition .= ' IS NULL';

		} elseif ($parameters instanceof Selection) { // where('column', $db->$table())
			$clone = clone $parameters;
			if (!$clone->select) {
				$clone->select = array($this->getPrimary($clone->name));
			}
			if ($this->connection->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'mysql') {
				$condition .= " IN ($clone)";
			} else {
				$in = array();
				foreach ($clone as $row) {
					$this->parameters[] = array_values(iterator_to_array($row));
					$in[] = (count($row) === 1 ? '?' : '(?)');
				}
				$condition .= ' IN (' . ($in ? implode(', ', $in) : 'NULL') . ')';
			}

		} elseif (!is_array($parameters)) { // where('column', 'x')
			$condition .= ' = ?';
			$this->parameters[] = $parameters;

		} else { // where('column', array(1, 2))
			if ($parameters) {
				$condition .= " IN (?)";
				$this->parameters[] = $parameters;
			} else {
				$condition .= " IN (NULL)";
			}
		}

		$this->where[] = $condition;
		return $this;
	}



	/**
	 * Adds order clause, more calls appends to the end.
	 * @param  string for example 'column1, column2 DESC'
	 * @return Selection provides a fluent interface
	 */
	public function order($columns)
	{
		$this->rows = NULL;
		$this->order[] = $this->tryDelimite($columns);
		return $this;
	}



	/**
	 * Sets limit clause, more calls rewrite old values.
	 * @param  int
	 * @param  int
	 * @return Selection provides a fluent interface
	 */
	public function limit($limit, $offset = NULL)
	{
		$this->rows = NULL;
		$this->limit = $limit;
		$this->offset = $offset;
		return $this;
	}



	/**
	 * Sets group clause, more calls rewrite old values.
	 * @param  string
	 * @param  string
	 * @return Selection provides a fluent interface
	 */
	public function group($columns, $having = '')
	{
		$this->__destruct();
		$this->group = $this->tryDelimite($columns);
		$this->having = $having;
		return $this;
	}



	/**
	 * Executes aggregation function.
	 * @param  string
	 * @return string
	 */
	public function aggregation($function)
	{
		$join = $this->createJoins(implode(',', $this->conditions), TRUE) + $this->createJoins($function);
		$query = "SELECT $function FROM $this->delimitedName" . implode($join);
		if ($this->where) {
			$query .= ' WHERE (' . implode(') AND (', $this->where) . ')';
		}
		foreach ($this->query($query)->fetch() as $val) {
			return $val;
		}
	}



	/**
	 * Counts number of rows.
	 * @param  string
	 * @return int
	 */
	public function count($column = '')
	{
		if (!$column) {
			$this->execute();
			return count($this->data);
		}
		return $this->aggregation("COUNT({$this->tryDelimite($column)})");
	}



	/**
	 * Returns minimum value from a column.
	 * @param  string
	 * @return int
	 */
	public function min($column)
	{
		return $this->aggregation("MIN({$this->tryDelimite($column)})");
	}



	/**
	 * Returns maximum value from a column.
	 * @param  string
	 * @return int
	 */
	public function max($column)
	{
		return $this->aggregation("MAX({$this->tryDelimite($column)})");
	}



	/**
	 * Returns sum of values in a column.
	 * @param  string
	 * @return int
	 */
	public function sum($column)
	{
		return $this->aggregation("SUM({$this->tryDelimite($column)})");
	}



	/**
	 * Returns SQL query.
	 * @return string
	 */
	public function getSql()
	{
		$join = $this->createJoins(implode(',', $this->conditions), TRUE)
			+ $this->createJoins(implode(',', $this->select) . ",$this->group,$this->having," . implode(',', $this->order));

		if ($this->rows === NULL && $this->connection->cache && !is_string($this->prevAccessed)) {
			$this->accessed = $this->prevAccessed = $this->connection->cache->load(array(__CLASS__, $this->name, $this->conditions));
		}

		$prefix = $join ? "$this->delimitedName." : '';
		if ($this->select) {
			$cols = implode(', ', $this->select);

		} elseif ($this->prevAccessed) {
			$cols = $prefix . implode(', ' . $prefix, array_map(array($this->connection->getSupplementalDriver(), 'delimite'), array_keys($this->prevAccessed)));

		} else {
			$cols = $prefix . '*';
		}

		return "SELECT{$this->topString()} $cols FROM $this->delimitedName" . implode($join) . $this->whereString();
	}



	protected function createJoins($val, $inner = FALSE)
	{
		$supplementalDriver = $this->connection->getSupplementalDriver();
		$joins = array();
		preg_match_all('~\\b(\\w+)\\.(\\w+)(\\s+IS\\b|\\s*<=>)?~i', $val, $matches, PREG_SET_ORDER);
		foreach ($matches as $match) {
			$name = $match[1];
			if ($name !== $this->name) { // case-sensitive
				$table = $this->connection->databaseReflection->getReferencedTable($name, $this->name);
				$column = $this->connection->databaseReflection->getReferencedColumn($name, $this->name);
				$primary = $this->getPrimary($table);
				$joins[$name] = ' ' . (!isset($joins[$name]) && $inner && !isset($match[3]) ? 'INNER' : 'LEFT')
					. ' JOIN ' . $supplementalDriver->delimite($table)
					. ($table !== $name ? ' AS ' . $supplementalDriver->delimite($name) : '')
					. " ON $this->delimitedName." . $supplementalDriver->delimite($column)
					. ' = ' . $supplementalDriver->delimite($name) . '.' . $supplementalDriver->delimite($primary);
			}
		}
		return $joins;
	}



	/**
	 * Executes built query.
	 * @return NULL
	 */
	protected function execute()
	{
		if ($this->rows !== NULL) {
			return;
		}

		try {
			$result = $this->query($this->getSql());

		} catch (\PDOException $exception) {
			if (!$this->select && $this->prevAccessed) {
				$this->prevAccessed = '';
				$this->accessed = array();
				$result = $this->query($this->getSql());
			} else {
				throw $exception;
			}
		}

		$this->rows = array();
		$result->setFetchMode(PDO::FETCH_ASSOC);
		foreach ($result as $key => $row) {
			$row = $result->normalizeRow($row);
			$this->rows[isset($row[$this->primary]) ? $row[$this->primary] : $key] = new ActiveRow($row, $this);
		}
		$this->data = $this->rows;

		if (isset($row[$this->primary]) && !is_string($this->accessed)) {
			$this->accessed[$this->primary] = TRUE;
		}
	}



	protected function whereString()
	{
		$return = '';
		$driver = $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME);
		$where = $this->where;
		if ($this->limit !== NULL && $driver === 'oci') {
			$where[] = ($this->offset ? "rownum > $this->offset AND " : '') . 'rownum <= ' . ($this->limit + $this->offset);
		}
		if ($where) {
			$return .= ' WHERE (' . implode(') AND (', $where) . ')';
		}
		if ($this->group) {
			$return .= " GROUP BY $this->group";
		}
		if ($this->having) {
			$return .= " HAVING $this->having";
		}
		if ($this->order) {
			$return .= ' ORDER BY ' . implode(', ', $this->order);
		}
		if ($this->limit !== NULL && $driver !== 'oci' && $driver !== 'dblib') {
			$return .= " LIMIT $this->limit";
			if ($this->offset !== NULL) {
				$return .= " OFFSET $this->offset";
			}
		}
		return $return;
	}



	protected function topString()
	{
		if ($this->limit !== NULL && $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME) === 'dblib') {
			return " TOP ($this->limit)"; //! offset is not supported
		}
		return '';
	}



	protected function tryDelimite($s)
	{
		return preg_match('#^[a-z_][a-z0-9_.]*$#i', $s) // is identifier?
			? implode('.', array_map(array($this->connection->getSupplementalDriver(), 'delimite'), explode('.', $s)))
			: $s;
	}



	protected function query($query)
	{
		return $this->connection->queryArgs($query, $this->parameters);
	}



	public function access($key, $delete = FALSE)
	{
		if ($delete) {
			if (is_array($this->accessed)) {
				$this->accessed[$key] = FALSE;
			}
			return FALSE;
		}

		if ($key === NULL) {
			$this->accessed = '';

		} elseif (!is_string($this->accessed)) {
			$this->accessed[$key] = TRUE;
		}

		if (!$this->select && $this->prevAccessed && ($key === NULL || !isset($this->prevAccessed[$key]))) {
			$this->prevAccessed = '';
			$this->rows = NULL;
			return TRUE;
		}
		return FALSE;
	}



	/********************* manipulation ****************d*g**/



	/**
	 * Inserts row in a table.
	 * @param  mixed array($column => $value)|Traversable for single row insert or TableSelection|string for INSERT ... SELECT
	 * @return ActiveRow or FALSE in case of an error or number of affected rows for INSERT ... SELECT
	 */
	public function insert($data)
	{
		if ($data instanceof Selection) {
			$data = $data->getSql();

		} elseif ($data instanceof \Traversable) {
			$data = iterator_to_array($data);
		}

		$return = $this->connection->query("INSERT INTO $this->delimitedName", $data);

		$this->rows = NULL;
		if (!is_array($data)) {
			return $return->rowCount();
		}

		if (!isset($data[$this->primary]) && ($id = $this->connection->lastInsertId())) {
			$data[$this->primary] = $id;
		}
		return new ActiveRow($data, $this);
	}



	/**
	 * Updates all rows in result set.
	 * @param  array ($column => $value)
	 * @return int number of affected rows or FALSE in case of an error
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
		// joins in UPDATE are supported only in MySQL
		return $this->connection->queryArgs(
			'UPDATE' . $this->topString() . " $this->delimitedName SET ?" . $this->whereString(),
			array_merge(array($data), $this->parameters)
		)->rowCount();
	}



	/**
	 * Deletes all rows in result set.
	 * @return int number of affected rows or FALSE in case of an error
	 */
	public function delete()
	{
		return $this->query(
			'DELETE' . $this->topString() . " FROM $this->delimitedName" . $this->whereString()
		)->rowCount();
	}



	/********************* references ****************d*g**/



	/**
	 * Returns referenced row.
	 * @param  string
	 * @return ActiveRow or NULL if the row does not exist
	 */
	public function getReferencedTable($name, & $column = NULL)
	{
		$column = $this->connection->databaseReflection->getReferencedColumn($name, $this->name);
		$referenced = & $this->referenced[$name];
		if ($referenced === NULL) {
			$keys = array();
			foreach ($this->rows as $row) {
				if ($row[$column] !== NULL) {
					$keys[$row[$column]] = NULL;
				}
			}
			if ($keys) {
				$table = $this->connection->databaseReflection->getReferencedTable($name, $this->name);
				$referenced = new Selection($table, $this->connection);
				$referenced->where($table . '.' . $this->getPrimary($table), array_keys($keys));
			} else {
				$referenced = array();
			}
		}
		return $referenced;
	}



	/**
	 * Returns referencing rows.
	 * @param  string table name
	 * @return GroupedSelection
	 */
	public function getReferencingTable($table)
	{
		$column = $this->connection->databaseReflection->getReferencingColumn($table, $this->name);
		$referencing = new GroupedSelection($table, $this, $column);
		$referencing->where("$table.$column", array_keys((array) $this->rows)); // (array) - is NULL after insert
		return $referencing;
	}



	private function getPrimary($table)
	{
		return $this->connection->databaseReflection->getPrimary($table);
	}



	/********************* interface Iterator ****************d*g**/



	public function rewind()
	{
		$this->execute();
		$this->keys = array_keys($this->data);
		reset($this->keys);
	}



	/** @return ActiveRow */
	public function current()
	{
		return $this->data[current($this->keys)];
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
	 * @param  ActiveRow
	 * @return NULL
	 */
	public function offsetSet($key, $value)
	{
		$this->execute();
		$this->data[$key] = $value;
	}



	/**
	 * Returns specified row.
	 * @param  string row ID
	 * @return ActiveRow or NULL if there is no such row
	 */
	public function offsetGet($key)
	{
		$this->execute();
		return $this->data[$key];
	}



	/**
	 * Tests if row exists.
	 * @param  string row ID
	 * @return bool
	 */
	public function offsetExists($key)
	{
		$this->execute();
		return isset($this->data[$key]);
	}



	/**
	 * Removes row from result set.
	 * @param  string row ID
	 * @return NULL
	 */
	public function offsetUnset($key)
	{
		$this->execute();
		unset($this->data[$key]);
	}



	/**
	 * Returns next row of result.
	 * @return ActiveRow or FALSE if there is no row
	 */
	public function fetch()
	{
		$this->execute();
		$return = current($this->data);
		next($this->data);
		return $return;
	}



	/**
	 * Returns all rows as associative array.
	 * @param  string
	 * @param  string column name used for an array value or an empty string for the whole row
	 * @return array
	 */
	public function fetchPairs($key, $value = '')
	{
		$return = array();
		// no $clone->select = array($key, $value) to allow efficient caching with repetitive calls with different parameters
		foreach ($this as $row) {
			$return[$row[$key]] = ($value !== '' ? $row[$value] : $row);
		}
		return $return;
	}

}
