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
	Nette\Utils\Strings,
	PDO;



/**
 * Builds SQL query.
 * SqlBuilder is based on great library NotORM http://www.notorm.com written by Jakub Vrana.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 */
class SqlBuilder extends Nette\Object
{
	/** @var Selection */
	protected $selection;

	/** @var Nette\Database\Connection */
	protected $connection;

	/** @var string delimited table name */
	protected $delimitedTable;

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



	public function __construct(Selection $selection)
	{
		$this->selection = $selection;
		$this->connection = $selection->getConnection();
		$this->delimitedTable = $this->connection->getSupplementalDriver()->delimite($selection->getName());
	}



	public function setSelection(Selection $selection)
	{
		$this->selection = $selection;
	}



	public function buildInsertQuery()
	{
		return "INSERT INTO {$this->delimitedTable}";
	}



	public function buildUpdateQuery()
	{
		return "UPDATE{$this->buildTopClause()} {$this->delimitedTable} SET ?" . $this->buildQueryConditions();
	}



	public function buildDeleteQuery()
	{
		return "DELETE{$this->buildTopClause()} FROM {$this->delimitedTable}" . $this->buildQueryConditions();
	}



	public function importConditions(SqlBuilder $builder)
	{
		$this->where = $builder->where;
		$this->parameters = $builder->parameters;
		$this->conditions = $builder->conditions;
	}



	/********************* SQL selectors ****************d*g**/



	public function addSelect($columns)
	{
		$this->select[] = $columns;
	}



	public function getSelect()
	{
		return $this->select;
	}



	public function addWhere($condition, $parameters = array())
	{
		$args = func_get_args();
		$hash = md5(json_encode($args));
		if (isset($this->conditions[$hash])) {
			return FALSE;
		}

		$this->conditions[$hash] = $condition;
		if (count($args) !== 2 || strpbrk($condition, '?:')) { // where('column < ? OR column > ?', array(1, 2))
			if (count($args) !== 2 || !is_array($parameters)) { // where('column < ? OR column > ?', 1, 2)
				$parameters = $args;
				array_shift($parameters);
			}

			$this->parameters = array_merge($this->parameters, $parameters);

		} elseif ($parameters === NULL) { // where('column', NULL)
			$condition .= ' IS NULL';

		} elseif ($parameters instanceof Selection) { // where('column', $db->$table())
			$clone = clone $parameters;
			if (!$clone->getSqlBuilder()->select) {
				$clone->select($clone->primary);
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
		return TRUE;
	}



	public function getConditions()
	{
		return array_values($this->conditions);
	}



	public function addOrder($columns)
	{
		$this->order[] = $columns;
	}



	public function getOrder()
	{
		return $this->order;
	}



	public function setLimit($limit, $offset)
	{
		$this->limit = $limit;
		$this->offset = $offset;
	}



	public function getLimit()
	{
		return $this->limit;
	}



	public function getOffset()
	{
		return $this->offset;
	}



	public function setGroup($columns, $having)
	{
		$this->group = $columns;
		$this->having = $having;
	}



	public function getGroup()
	{
		return $this->group;
	}



	public function getHaving()
	{
		return $this->having;
	}



	/********************* SQL building ****************d*g**/



	/**
	 * Returns SQL select query.
	 * @return string
	 */
	public function buildSelectQuery()
	{
		$queryCondition = $this->buildQueryConditions();
		$queryEnd       = $this->buildQueryEnd();

		$joins = array();
		$this->parseJoins($joins, $queryCondition, TRUE);
		$this->parseJoins($joins, $queryEnd);

		if ($this->select) {
			$querySelect = $this->buildQuerySelect($this->select);
			$this->parseJoins($joins, $querySelect);

		} elseif ($prevAccessed = $this->selection->getPreviousAccessed()) {
			$prefix = $joins ? "{$this->delimitedTable}." : '';
			$cols = array();
			foreach (array_keys(array_filter($prevAccessed)) as $col) {
				$cols[] = $prefix . $col;
			}
			$querySelect = $this->buildQuerySelect($cols);

		} elseif ($this->group) {
			$querySelect = $this->buildQuerySelect(array($this->group));
			$this->parseJoins($joins, $querySelect);

		} else {
			$prefix = $joins ? "{$this->delimitedTable}." : '';
			$querySelect = $this->buildQueryselect(array($prefix . '*'));

		}

		$queryJoins = $this->buildQueryJoins($joins);
		$query = "{$querySelect} FROM {$this->delimitedTable}{$queryJoins}{$queryCondition}{$queryEnd}";

		return $this->tryDelimite($query);
	}



	public function getParameters()
	{
		return $this->parameters;
	}



	protected function parseJoins(& $joins, & $query, $inner = FALSE)
	{
		$builder = $this;
		$query = Strings::replace($query, '~
			(?(DEFINE)
				(?<word> [a-z][\w_]* )
				(?<del> [.:] )
				(?<pair> \( (?&word) (?:\.|\s*,\s*) (?&word) \) )
				(?<node> (?&del)? (?: (?&word) | (?&pair) ) )
			)

			(?<chain> (?&node)*)  \. (?<column> (?&word) | \*  )

		~xi', function($match) use (& $joins, $inner, $builder) {
			return $builder->parseJoinsCb($joins, $match, $inner);
		});
	}



	public function parseJoinsCb(& $joins, $match, $inner)
	{
		$reflection = $this->selection->getConnection()->getDatabaseReflection();

		$chain = $match['chain'];
		if ($chain[0] !== '.' || $chain[0] !== ':') {
			$chain = '.' . $chain;  // unified chain format
		}

		$parent = $this->selection->getName();
		if ($chain == ".{$parent}") { // case-sensitive
			return "{$parent}.{$match['column']}";
		}

		$keyMatches = Strings::matchAll($chain, '~
			(?(DEFINE)
				(?<word> [a-z][\w_]* )
			)
			(?<del> [.:])?
			(?:
				(?<key> (?&word)) |
				\(
					(?<table> (?&word))
					(?:\.|\s*,\s*)
					(?<column> (?&word))
				\)
			)
		~xi');

		foreach ($keyMatches as $keyMatch) {
			$name = $keyMatch['key'];

			if ($keyMatch['del'] === ':') {
				if (!empty($name)) {
					list($table, $primary) = $reflection->getHasManyReference($parent, $name);
				} else {
					$table = $keyMatch['table'];
					$primary = $keyMatch['column'];
				}

				$column = $reflection->getPrimary($parent);
			} else {
				if (!empty($name)) {
					list($table, $column) = $reflection->getBelongsToReference($parent, $name);
				} else {
					$table = $keyMatch['table'];
					$column = $keyMatch['column'];
				}

				$primary = $reflection->getPrimary($table);
			}

			$joins[$table] = array($table, $name ?: $table, $parent, $column, $primary, !isset($joins[$table]) && $inner);
			$parent = $table;
		}

		return ($name ?: $table) . ".{$match['column']}";
	}



	protected function buildQueryJoins($joins)
	{
		$return = '';
		foreach ($joins as $join) {
			list($joinTable, $joinAlias, $table, $tableColumn, $joinColumn, $inner) = $join;

			$return .= ' ' . ($inner ? 'INNER' : 'LEFT')
				. " JOIN {$joinTable}" . ($joinTable !== $joinAlias ? " AS {$joinAlias}" : '')
				. " ON {$table}.{$tableColumn} = {$joinAlias}.{$joinColumn}";
		}

		return $return;
	}



	protected function buildQueryConditions()
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

		return $return;
	}



	protected function buildQueryEnd()
	{
		$return = '';
		$driver = $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME);

		if ($this->group) {
			$return .= ' GROUP BY '. $this->group;
		}
		if ($this->having) {
			$return .= ' HAVING '. $this->having;
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



	protected function buildTopClause()
	{
		if ($this->limit !== NULL && $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME) === 'dblib') {
			return "TOP ({$this->limit} "; //! offset is not supported
		}
	}



	protected function buildQuerySelect($columns)
	{
		return 'SELECT ' . $this->buildTopClause() . implode(', ', $columns);
	}



	protected function tryDelimite($s)
	{
		$driver = $this->connection->getSupplementalDriver();
		return preg_replace_callback('#(?<=[^\w`"\[]|^)[a-z_][a-z0-9_]*(?=[^\w`"(\]]|$)#i', function($m) use ($driver) {
			return strtoupper($m[0]) === $m[0] ? $m[0] : $driver->delimite($m[0]);
		}, $s);
	}

}
