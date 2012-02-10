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
	PDO;



/**
 * Filtered table representation.
 * Selection is based on the great library NotORM http://www.notorm.com written by Jakub Vrana.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 *
 * @property-read string $sql
 */
class Selection extends Nette\Object implements \Iterator, \ArrayAccess, \Countable
{
	/** @var Nette\Database\Connection */
	protected $connection;

	/** @var string table name */
	protected $name;

	/** @var string primary key field name */
	protected $primary;

	/** @var ActiveRow[] data read from database in [primary key => ActiveRow] format */
	protected $rows;

	/** @var ActiveRow[] modifiable data in [primary key => ActiveRow] format */
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

	/** @var bool recheck referencing keys */
	protected $checkReferenceNewKeys = FALSE;

	/** @var Selection[] */
	protected $referenced = array();

	/** @var GroupedSelection[] */
	protected $referencing = array();

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



	public function __construct($table, Nette\Database\Connection $connection)
	{
		$this->name = $table;
		$this->connection = $connection;
		$this->primary = $connection->getDatabaseReflection()->getPrimary($table);
		$this->delimitedName = $this->tryDelimite($this->name);
		$this->delimitedPrimary = $connection->getSupplementalDriver()->delimite($this->primary);
	}



	/**
	 * Saves data to cache and empty result.
	 */
	public function __destruct()
	{
		$cache = $this->connection->getCache();
		if ($cache && !$this->select && $this->rows !== NULL) {
			$cache->save(array(__CLASS__, $this->name, $this->conditions), $this->accessed);
		}
		$this->rows = NULL;
	}



	/**
	 * @return Nette\Database\Connection
	 */
	public function getConnection()
	{
		return $this->connection;
	}



	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}



	/**
	 * @return string
	 */
	public function getPrimary()
	{
		return $this->primary;
	}



	/**
	 * Returns row specified by primary key.
	 * @param  mixed
	 * @return ActiveRow or FALSE if there is no such row
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
		$this->select[] = $columns;
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
				if (is_int($key)) {
					$this->where($val);	// where('full condition')
				} else {
					$this->where($key, $val);	// where('column', 1)
				}
			}
			return $this;
		}

		$hash = md5(json_encode(func_get_args()));
		if (isset($this->conditions[$hash])) {
			return $this;
		}

		$this->__destruct();

		$this->conditions[$hash] = $condition;
		$condition = $this->removeExtraTables($condition);
		$condition = $this->tryDelimite($condition);

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
				$clone->select = array($clone->primary);
			}
			if ($this->connection->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'mysql') {
				$condition .= ' IN (' . $clone->getSql() . ')';
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
		$this->order[] = $columns;
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
	 * Sets offset using page number, more calls rewrite old values.
	 * @param  int
	 * @param  int
	 * @return Selection provides a fluent interface
	 */
	public function page($page, $itemsPerPage)
	{
		$this->rows = NULL;
		$this->limit = $itemsPerPage;
		$this->offset = ($page - 1) * $itemsPerPage;
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
		$this->group = $columns;
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
		$selection = new Selection($this->name, $this->connection);
		$selection->where = $this->where;
		$selection->parameters = $this->parameters;
		$selection->conditions = $this->conditions;

		$selection->select($function);

		foreach ($selection->fetch() as $val) {
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



	/**
	 * Returns SQL query.
	 * @return string
	 */
	public function getSql()
	{
		$join = $this->createJoins(implode(',', $this->conditions), TRUE)
			+ $this->createJoins(implode(',', $this->select) . ",$this->group,$this->having," . implode(',', $this->order));

		$cache = $this->connection->getCache();
		if ($this->rows === NULL && $cache && !is_string($this->prevAccessed)) {
			$this->accessed = $this->prevAccessed = $cache->load(array(__CLASS__, $this->name, $this->conditions));
		}

		$prefix = $join ? "$this->delimitedName." : '';
		if ($this->select) {
			$cols = $this->tryDelimite($this->removeExtraTables(implode(', ', $this->select)));

		} elseif ($this->prevAccessed) {
			$cols = array_map(array($this->connection->getSupplementalDriver(), 'delimite'), array_keys(array_filter($this->prevAccessed)));
			$cols = $prefix . implode(', ' . $prefix, $cols);

		} else {
			$cols = $prefix . '*';
		}

		return "SELECT{$this->topString()} $cols FROM $this->delimitedName" . implode($join) . $this->whereString();
	}



	protected function createJoins($val, $inner = FALSE)
	{
		$driver = $this->connection->getSupplementalDriver();
		$reflection = $this->connection->getDatabaseReflection();
		$joins = array();
		preg_match_all('~\\b([a-z][\\w.:]*[.:])([a-z]\\w*|\*)(\\s+IS\\b|\\s*<=>)?~i', $val, $matches);
		foreach ($matches[1] as $names) {
			$parent = $this->name;
			if ($names !== "$parent.") { // case-sensitive
				preg_match_all('~\\b([a-z][\\w]*|\*)([.:])~i', $names, $matches, PREG_SET_ORDER);
				foreach ($matches as $match) {
					list(, $name, $delimiter) = $match;

					if ($delimiter === ':') {
						list($table, $primary) = $reflection->getHasManyReference($parent, $name);
						$column = $reflection->getPrimary($parent);
					} else {
						list($table, $column) = $reflection->getBelongsToReference($parent, $name);
						$primary = $reflection->getPrimary($table);
					}

					$joins[$name] = ' '
						. (!isset($joins[$name]) && $inner && !isset($match[3]) ? 'INNER' : 'LEFT')
						. ' JOIN ' . $driver->delimite($table) . ($table !== $name ? ' AS ' . $driver->delimite($name) : '')
						. ' ON ' . $driver->delimite($parent) . '.' . $driver->delimite($column)
						. ' = ' . $driver->delimite($name) . '.' . $driver->delimite($primary);

					$parent = $name;
				}
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
			$this->rows[isset($row[$this->primary]) ? $row[$this->primary] : $key] = $this->createRow($row);
		}
		$this->data = $this->rows;

		if (isset($row[$this->primary]) && !is_string($this->accessed)) {
			$this->accessed[$this->primary] = TRUE;
		}
	}



	protected function createRow(array $row)
	{
		return new ActiveRow($row, $this);
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
			$return .= ' GROUP BY '. $this->tryDelimite($this->removeExtraTables($this->group));
		}
		if ($this->having) {
			$return .= ' HAVING '. $this->tryDelimite($this->removeExtraTables($this->having));
		}
		if ($this->order) {
			$return .= ' ORDER BY ' . $this->tryDelimite($this->removeExtraTables(implode(', ', $this->order)));
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
		$driver = $this->connection->getSupplementalDriver();
		return preg_replace_callback('#(?<=[^\w`"\[]|^)[a-z_][a-z0-9_]*(?=[^\w`"(\]]|$)#i', function($m) use ($driver) {
			return strtoupper($m[0]) === $m[0] ? $m[0] : $driver->delimite($m[0]);
		}, $s);
	}



	protected function removeExtraTables($expression)
	{
		return preg_replace('~(?:\\b[a-z_][a-z0-9_.:]*[.:])?([a-z_][a-z0-9_]*)[.:]([a-z_*])~i', '\\1.\\2', $expression); // rewrite tab1.tab2.col
	}



	protected function query($query)
	{
		return $this->connection->queryArgs($query, $this->parameters);
	}



	/**
	 * @internal
	 * @param  string        column name
	 * @param  bool|NULL     TRUE - cache, FALSE - don't cache, NULL - remove
	 * @return bool
	 */
	public function access($key, $cache = TRUE)
	{
		if ($cache === NULL) {
			if (is_array($this->accessed)) {
				$this->accessed[$key] = FALSE;
			}
			return FALSE;
		}

		if ($key === NULL) {
			$this->accessed = '';

		} elseif (!is_string($this->accessed)) {
			$this->accessed[$key] = $cache;
		}

		if ($cache && !$this->select && $this->prevAccessed && ($key === NULL || !isset($this->prevAccessed[$key]))) {
			$this->prevAccessed = '';
			$this->__destruct();
			return TRUE;
		}
		return FALSE;
	}



	/********************* manipulation ****************d*g**/



	/**
	 * Inserts row in a table.
	 * @param  mixed array($column => $value)|Traversable for single row insert or Selection|string for INSERT ... SELECT
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

		if (!is_array($data)) {
			return $return->rowCount();
		}

		$this->checkReferenceNewKeys = TRUE;

		if (!isset($data[$this->primary]) && ($id = $this->connection->lastInsertId())) {
			$data[$this->primary] = $id;
			return $this->rows[$id] = new ActiveRow($data, $this);

		} else {
			return new ActiveRow($data, $this);

		}
	}



	/**
	 * Updates all rows in result set.
	 * @param  array|\Traversable ($column => $value)
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
	 * @param  string
	 * @param  bool  checks if rows contains the same primary value relations
	 * @return Selection or array() if the row does not exist
	 */
	public function getReferencedTable($table, $column, $checkReferenceNewKeys = FALSE)
	{
		$referenced = & $this->referenced["$table.$column"];
		if ($referenced === NULL || $checkReferenceNewKeys || $this->checkReferenceNewKeys) {
			$keys = array();
			foreach ($this->rows as $row) {
				if ($row[$column] === NULL)
					continue;

				$key = $row[$column] instanceof ActiveRow ? $row[$column]->getPrimary() : $row[$column];
				$keys[$key] = TRUE;
			}

			if ($referenced !== NULL && $keys === array_keys($this->rows)) {
				$this->checkReferenceNewKeys = FALSE;
				return $referenced;
			}

			if ($keys) {
				$referenced = new Selection($table, $this->connection);
				$referenced->where($table . '.' . $referenced->primary, array_keys($keys));
			} else {
				$referenced = array();
			}
		}

		return $referenced;
	}



	/**
	 * Returns referencing rows.
	 * @param  string
	 * @param  string
	 * @param  int  primary key
	 * @param  bool force new instance
	 * @return GroupedSelection
	 */
	public function getReferencingTable($table, $column, $active = NULL, $forceNewInstance = FALSE)
	{
		$referencing = & $this->referencing["$table:$column"];
		if (!$referencing || $forceNewInstance) {
			$referencing = new GroupedSelection($table, $this, $column);
			$referencing->where("$table.$column", array_keys((array) $this->rows)); // (array) - is NULL after insert
		}

		return $referencing->setActive($active);
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
			$return[is_object($row[$key]) ? (string) $row[$key] : $row[$key]] = ($value !== '' ? $row[$value] : $row);
		}
		return $return;
	}

}
