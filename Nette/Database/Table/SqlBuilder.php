<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Database\Table;

use Nette,
	Nette\Database\Connection,
	Nette\Database\IReflection,
	Nette\Database\ISupplementalDriver,
	Nette\Database\SqlLiteral;


/**
 * Builds SQL query.
 * SqlBuilder is based on great library NotORM http://www.notorm.com written by Jakub Vrana.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 */
class SqlBuilder extends Nette\Object
{
	/** @var Nette\Database\ISupplementalDriver */
	private $driver;

	/** @var string */
	protected $tableName;

	/** @var IReflection */
	protected $databaseReflection;

	/** @var string delimited table name */
	protected $delimitedTable;

	/** @var array of column to select */
	protected $select = array();

	/** @var array of where conditions */
	protected $where = array();

	/** @var array of where conditions for caching */
	protected $conditions = array();

	/** @var array of parameters passed to where conditions */
	protected $parameters = array(
		'select' => array(),
		'where' => array(),
		'group' => array(),
		'having' => array(),
		'order' => array(),
	);

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


	public function __construct($tableName, Connection $connection, IReflection $reflection)
	{
		$this->tableName = $tableName;
		$this->databaseReflection = $reflection;
		$this->driver = $connection->getSupplementalDriver();
		$this->delimitedTable = $this->tryDelimite($tableName);
	}


	public function buildInsertQuery()
	{
		return "INSERT INTO {$this->delimitedTable}";
	}


	public function buildUpdateQuery()
	{
		if ($this->limit !== NULL || $this->offset) {
			throw new Nette\NotSupportedException('LIMIT clause is not supported in UPDATE query.');
		}
		return $this->tryDelimite("UPDATE {$this->tableName} SET ?" . $this->buildConditions());
	}


	public function buildDeleteQuery()
	{
		if ($this->limit !== NULL || $this->offset) {
			throw new Nette\NotSupportedException('LIMIT clause is not supported in DELETE query.');
		}
		return $this->tryDelimite("DELETE FROM {$this->tableName}" . $this->buildConditions());
	}


	/**
	 * Returns SQL query.
	 * @param  string list of columns
	 * @return string
	 */
	public function buildSelectQuery($columns = NULL)
	{
		$queryCondition = $this->buildConditions();
		$queryEnd       = $this->buildQueryEnd();

		$joins = array();
		$this->parseJoins($joins, $queryCondition);
		$this->parseJoins($joins, $queryEnd);

		if ($this->select) {
			$querySelect = $this->buildSelect($this->select);
			$this->parseJoins($joins, $querySelect);

		} elseif ($columns) {
			$prefix = $joins ? "{$this->delimitedTable}." : '';
			$cols = array();
			foreach ($columns as $col) {
				$cols[] = $prefix . $col;
			}
			$querySelect = $this->buildSelect($cols);

		} elseif ($this->group && !$this->driver->isSupported(ISupplementalDriver::SUPPORT_SELECT_UNGROUPED_COLUMNS)) {
			$querySelect = $this->buildSelect(array($this->group));
			$this->parseJoins($joins, $querySelect);

		} else {
			$prefix = $joins ? "{$this->delimitedTable}." : '';
			$querySelect = $this->buildSelect(array($prefix . '*'));

		}

		$queryJoins = $this->buildQueryJoins($joins);
		$query = "{$querySelect} FROM {$this->tableName}{$queryJoins}{$queryCondition}{$queryEnd}";

		if ($this->limit !== NULL || $this->offset) {
			$this->driver->applyLimit($query, $this->limit, $this->offset);
		}

		return $this->tryDelimite($query);
	}


	public function getParameters()
	{
		return array_merge(
			$this->parameters['select'],
			$this->parameters['where'],
			$this->parameters['group'],
			$this->parameters['having'],
			$this->parameters['order']
		);
	}


	public function importConditions(SqlBuilder $builder)
	{
		$this->where = $builder->where;
		$this->parameters['where'] = $builder->parameters['where'];
		$this->conditions = $builder->conditions;
	}


	/********************* SQL selectors ****************d*g**/


	public function addSelect($columns)
	{
		if (is_array($columns)) {
			throw new Nette\InvalidArgumentException('Select column must be a string.');
		}
		$this->select[] = $columns;
		$this->parameters['select'] = array_merge($this->parameters['select'], array_slice(func_get_args(), 1));
	}


	public function getSelect()
	{
		return $this->select;
	}


	public function addWhere($condition, $parameters = array())
	{
		if (is_array($condition) && is_array($parameters) && !empty($parameters)) {
			return $this->addWhereComposition($condition, $parameters);
		}

		$args = func_get_args();
		$hash = md5(json_encode($args));
		if (isset($this->conditions[$hash])) {
			return FALSE;
		}

		$this->conditions[$hash] = $condition;
		$placeholderCount = substr_count($condition, '?');
		if ($placeholderCount > 1 && count($args) === 2 && is_array($parameters)) {
			$args = $parameters;
		} else {
			array_shift($args);
		}

		$condition = trim($condition);
		if ($placeholderCount === 0 && count($args) === 1) {
			$condition .= ' ?';
		} elseif ($placeholderCount !== count($args)) {
			throw new Nette\InvalidArgumentException('Argument count does not match placeholder count.');
		}

		$replace = NULL;
		$placeholderNum = 0;
		foreach ($args as $arg) {
			preg_match('#(?:.*?\?.*?){' . $placeholderNum . '}(((?:&|\||^|~|\+|-|\*|/|%|\(|,|<|>|=|(?<=\W|^)(?:REGEXP|ALL|AND|ANY|BETWEEN|EXISTS|IN|[IR]?LIKE|OR|NOT|SOME|INTERVAL))\s*)?(?:\(\?\)|\?))#s', $condition, $match, PREG_OFFSET_CAPTURE);
			$hasOperator = ($match[1][0] === '?' && $match[1][1] === 0) ? TRUE : !empty($match[2][0]);

			if ($arg === NULL) {
				if ($hasOperator) {
					throw new Nette\InvalidArgumentException('Column operator does not accept NULL argument.');
				}
				$replace = 'IS NULL';
			} elseif (is_array($arg) || $arg instanceof Selection) {
				if ($hasOperator) {
					if (trim($match[2][0]) === 'NOT') {
						$match[2][0] = rtrim($match[2][0]) . ' IN ';
					} elseif (trim($match[2][0]) !== 'IN') {
						throw new Nette\InvalidArgumentException('Column operator does not accept array argument.');
					}
				} else {
					$match[2][0] = 'IN ';
				}

				if ($arg instanceof Selection) {
					$clone = clone $arg;
					if (!$clone->getSqlBuilder()->select) {
						try {
							$clone->select($clone->getPrimary());
						} catch (\LogicException $e) {
							throw new Nette\InvalidArgumentException('Selection argument must have defined a select column.', 0, $e);
						}
					}

					if ($this->driver->isSupported(ISupplementalDriver::SUPPORT_SUBSELECT)) {
						$arg = NULL;
						$replace = $match[2][0] . '(' . $clone->getSql() . ')';
						$this->parameters['where'] = array_merge($this->parameters['where'], $clone->getSqlBuilder()->parameters['where']);
					} else {
						$arg = array();
						foreach ($clone as $row) {
							$arg[] = array_values(iterator_to_array($row));
						}
					}
				}

				if ($arg !== NULL) {
					if (!$arg) {
						$hasBrackets = strpos($condition, '(') !== FALSE;
						$hasOperators = preg_match('#AND|OR#', $condition);
						$hasNot = strpos($condition, 'NOT') !== FALSE;
						$hasPrefixNot = strpos($match[2][0], 'NOT') !== FALSE;
						if (!$hasBrackets && ($hasOperators || ($hasNot && !$hasPrefixNot))) {
							throw new Nette\InvalidArgumentException('Possible SQL query corruption. Add parentheses around operators.');
						}
						if ($hasPrefixNot) {
							$replace = 'IS NULL OR TRUE';
						} else {
							$replace = 'IS NULL AND FALSE';
						}
						$arg = NULL;
					} else {
						$replace = $match[2][0] . '(?)';
						$this->parameters['where'][] = $arg;
					}
				}
			} elseif ($arg instanceof SqlLiteral) {
				$this->parameters['where'][] = $arg;
			} else {
				if (!$hasOperator) {
					$replace = '= ?';
				}
				$this->parameters['where'][] = $arg;
			}

			if ($replace) {
				$condition = substr_replace($condition, $replace, $match[1][1], strlen($match[1][0]));
				$replace = NULL;
			}

			if ($arg !== NULL) {
				$placeholderNum++;
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
		$this->parameters['order'] = array_merge($this->parameters['order'], array_slice(func_get_args(), 1));
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


	public function setGroup($columns)
	{
		$this->group = $columns;
		$this->parameters['group'] = array_slice(func_get_args(), 1);
	}


	public function getGroup()
	{
		return $this->group;
	}


	public function setHaving($having)
	{
		$this->having = $having;
		$this->parameters['having'] = array_slice(func_get_args(), 1);
	}


	public function getHaving()
	{
		return $this->having;
	}


	/********************* SQL building ****************d*g**/


	protected function buildSelect(array $columns)
	{
		return 'SELECT ' . implode(', ', $columns);
	}


	protected function parseJoins(& $joins, & $query)
	{
		$builder = $this;
		$query = preg_replace_callback('~
			(?(DEFINE)
				(?P<word> [a-z][\w_]* )
				(?P<del> [.:] )
				(?P<node> (?&del)? (?&word) (\((?&word)\))? )
			)
			(?P<chain> (?!\.) (?&node)*)  \. (?P<column> (?&word) | \*  )
		~xi', function($match) use (& $joins, $builder) {
			return $builder->parseJoinsCb($joins, $match);
		}, $query);
	}


	public function parseJoinsCb(& $joins, $match)
	{
		$chain = $match['chain'];
		if (!empty($chain[0]) && ($chain[0] !== '.' || $chain[0] !== ':')) {
			$chain = '.' . $chain;  // unified chain format
		}

		$parent = $parentAlias = $this->tableName;
		if ($chain == ".{$parent}") { // case-sensitive
			return "{$parent}.{$match['column']}";
		}

		preg_match_all('~
			(?(DEFINE)
				(?P<word> [a-z][\w_]* )
			)
			(?P<del> [.:])?(?P<key> (?&word))(\((?P<throughColumn> (?&word))\))?
		~xi', $chain, $keyMatches, PREG_SET_ORDER);

		foreach ($keyMatches as $keyMatch) {
			if ($keyMatch['del'] === ':') {
				if (isset($keyMatch['throughColumn'])) {
					$table = $keyMatch['key'];
					list(, $primary) = $this->databaseReflection->getBelongsToReference($table, $keyMatch['throughColumn']);
				} else {
					list($table, $primary) = $this->databaseReflection->getHasManyReference($parent, $keyMatch['key']);
				}
				$column = $this->databaseReflection->getPrimary($parent);
			} else {
				list($table, $column) = $this->databaseReflection->getBelongsToReference($parent, $keyMatch['key']);
				$primary = $this->databaseReflection->getPrimary($table);
			}

			$joins[$table . $column] = array($table, $keyMatch['key'] ?: $table, $parentAlias, $column, $primary);
			$parent = $table;
			$parentAlias = $keyMatch['key'];
		}

		return ($keyMatch['key'] ?: $table) . ".{$match['column']}";
	}


	protected function buildQueryJoins(array $joins)
	{
		$return = '';
		foreach ($joins as $join) {
			list($joinTable, $joinAlias, $table, $tableColumn, $joinColumn) = $join;

			$return .=
				" LEFT JOIN {$joinTable}" . ($joinTable !== $joinAlias ? " AS {$joinAlias}" : '') .
				" ON {$table}.{$tableColumn} = {$joinAlias}.{$joinColumn}";
		}

		return $return;
	}


	protected function buildConditions()
	{
		return $this->where ? ' WHERE (' . implode(') AND (', $this->where) . ')' : '';
	}


	protected function buildQueryEnd()
	{
		$return = '';
		if ($this->group) {
			$return .= ' GROUP BY '. $this->group;
		}
		if ($this->having) {
			$return .= ' HAVING '. $this->having;
		}
		if ($this->order) {
			$return .= ' ORDER BY ' . implode(', ', $this->order);
		}
		return $return;
	}


	protected function tryDelimite($s)
	{
		$driver = $this->driver;
		return preg_replace_callback('#(?<=[^\w`"\[]|^)[a-z_][a-z0-9_]*(?=[^\w`"(\]]|\z)#i', function($m) use ($driver) {
			return strtoupper($m[0]) === $m[0] ? $m[0] : $driver->delimite($m[0]);
		}, $s);
	}


	protected function addWhereComposition(array $columns, array $parameters)
	{
		if ($this->driver->isSupported(ISupplementalDriver::SUPPORT_MULTI_COLUMN_AS_OR_COND)) {
			$conditionFragment = '(' . implode(' = ? AND ', $columns) . ' = ?) OR ';
			$condition = substr(str_repeat($conditionFragment, count($parameters)), 0, -4);
			return $this->addWhere($condition, Nette\Utils\Arrays::flatten($parameters));
		} else {
			return $this->addWhere('(' . implode(', ', $columns) . ') IN', $parameters);
		}
	}

}
