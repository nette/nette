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
	private $driverName;

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



	public function __construct($tableName, Connection $connection, IReflection $reflection)
	{
		$this->tableName = $tableName;
		$this->databaseReflection = $reflection;
		$this->driver = $connection->getSupplementalDriver();
		$this->driverName = $connection->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);
		$this->delimitedTable = $this->tryDelimite($tableName);
	}



	public function buildInsertQuery()
	{
		return "INSERT INTO {$this->delimitedTable}";
	}



	public function buildUpdateQuery()
	{
		return "UPDATE{$this->buildTopClause()} {$this->delimitedTable} SET ?" . $this->buildConditions();
	}



	public function buildDeleteQuery()
	{
		return "DELETE{$this->buildTopClause()} FROM {$this->delimitedTable}" . $this->buildConditions();
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
		if (is_array($columns)) {
			throw new Nette\InvalidArgumentException('Select column must be a string.');
		}
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
		$condition = $this->removeExtraTables($condition);
		$condition = $this->tryDelimite($condition);

		$placeholderCount = substr_count($condition, '?');
		if ($placeholderCount > 1 && count($args) === 2 && is_array($parameters)) {
			$args = $parameters;
		} else {
			array_shift($args);
		}

		if ($placeholderCount === 0 && count($args) === 1) {
			$condition .= ' ?';
		} elseif ($placeholderCount !== count($args)) {
			throw new Nette\InvalidArgumentException('Argument count does not match placeholder count.');
		}

		$replace = NULL;
		$placeholderNum = 0;
		foreach ($args as $arg) {
			preg_match('#(?:.*?\?.*?){' . $placeholderNum . '}(((?:&|\||^|~|\+|-|\*|/|%|\(|,|<|>|=|ALL|AND|ANY|BETWEEN|EXISTS|IN|LIKE|OR|NOT|SOME)\s*)?\?)#', $condition, $match, PREG_OFFSET_CAPTURE);
			$hasOperator = ($match[1][0] === '?' && $match[1][1] === 0) ? TRUE : !empty($match[2][0]);

			if ($arg === NULL) {
				if ($hasOperator) {
					throw new Nette\InvalidArgumentException('Column operator does not accept NULL argument.');
				}
				$replace = 'IS NULL';
			} elseif ($arg instanceof Selection) {
				$clone = clone $arg;
				if (!$clone->getSqlBuilder()->select) {
					try {
						$clone->select($clone->getPrimary());
					} catch (\LogicException $e) {
						throw new Nette\InvalidArgumentException('Selection argument must have defined a select column.', 0, $e);
					}
				}

				if ($this->driverName !== 'mysql') {
					$replace = 'IN (' . $clone->getSql() . ')';
					$this->parameters = array_merge($this->parameters, $clone->getSqlBuilder()->getParameters());
				} else {
					$parameter = array();
					foreach ($clone as $row) {
						$parameter[] = array_values(iterator_to_array($row));
					}

					if (!$parameter) {
						$replace = 'IN (NULL)';
					}  else {
						$replace = 'IN (?)';
						$this->parameters[] = $parameter;
					}
				}
			} elseif ($arg instanceof SqlLiteral) {
				$this->parameters[] = $arg;
			} elseif (is_array($arg)) {
				if ($hasOperator) {
					if (trim($match[2][0]) !== 'IN') {
						throw new Nette\InvalidArgumentException('Column operator does not accept array argument.');
					}
				} else {
					$match[2][0] = 'IN ';
				}

				if (!$arg) {
					$replace = $match[2][0] . '(NULL)';
				} else {
					$replace = $match[2][0] . '(?)';
					$this->parameters[] = $arg;
				}
			} else {
				if ($hasOperator) {
					$replace = $match[2][0] . '?';
				} else {
					$replace = '= ?';
				}
				$this->parameters[] = $arg;
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
	}



	public function getGroup()
	{
		return $this->group;
	}



	public function setHaving($having)
	{
		$this->having = $having;
	}



	public function getHaving()
	{
		return $this->having;
	}



	/********************* SQL building ****************d*g**/



	/**
	 * Returns SQL query.
	 * @param  list of columns
	 * @return string
	 */
	public function buildSelectQuery($columns = NULL)
	{
		$join = $this->buildJoins(implode(',', $this->conditions), TRUE);
		$join += $this->buildJoins(implode(',', $this->select) . ",{$this->group},{$this->having}," . implode(',', $this->order));

		$prefix = $join ? "{$this->delimitedTable}." : '';
		if ($this->select) {
			$cols = $this->tryDelimite($this->removeExtraTables(implode(', ', $this->select)));

		} elseif ($columns) {
			$cols = array_map(array($this->driver, 'delimite'), $columns);
			$cols = $prefix . implode(', ' . $prefix, $cols);

		} elseif ($this->group && !$this->driver->isSupported(ISupplementalDriver::SUPPORT_SELECT_UNGROUPED_COLUMNS)) {
			$cols = $this->tryDelimite($this->removeExtraTables($this->group));

		} else {
			$cols = $prefix . '*';
		}

		return "SELECT{$this->buildTopClause()} {$cols} FROM {$this->delimitedTable}" . implode($join) . $this->buildConditions();
	}



	public function getParameters()
	{
		return $this->parameters;
	}



	protected function buildJoins($val, $inner = FALSE)
	{
		$joins = array();
		preg_match_all('~\\b([a-z][\\w.:]*[.:])([a-z]\\w*|\*)(\\s+IS\\b|\\s*<=>)?~i', $val, $matches);
		foreach ($matches[1] as $names) {
			$parent = $parentAlias = $this->tableName;
			if ($names !== "$parent.") { // case-sensitive
				preg_match_all('~\\b([a-z][\\w]*|\*)([.:])~i', $names, $matches, PREG_SET_ORDER);
				foreach ($matches as $match) {
					list(, $name, $delimiter) = $match;

					if ($delimiter === ':') {
						list($table, $primary) = $this->databaseReflection->getHasManyReference($parent, $name);
						$column = $this->databaseReflection->getPrimary($parent);
					} else {
						list($table, $column) = $this->databaseReflection->getBelongsToReference($parent, $name);
						$primary = $this->databaseReflection->getPrimary($table);
					}

					$joins[$name] = ' '
						. (!isset($joins[$name]) && $inner && !isset($match[3]) ? 'INNER' : 'LEFT')
						. ' JOIN ' . $this->driver->delimite($table) . ($table !== $name ? ' AS ' . $this->driver->delimite($name) : '')
						. ' ON ' . $this->driver->delimite($parentAlias) . '.' . $this->driver->delimite($column)
						. ' = ' . $this->driver->delimite($name) . '.' . $this->driver->delimite($primary);

					$parent = $table;
					$parentAlias = $name;
				}
			}
		}
		return $joins;
	}



	protected function buildConditions()
	{
		$return = '';
		$where = $this->where;
		if ($this->limit !== NULL && $this->driverName === 'oci') {
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
		if ($this->limit !== NULL && $this->driverName !== 'oci' && $this->driverName !== 'dblib') {
			$return .= " LIMIT $this->limit";
			if ($this->offset !== NULL) {
				$return .= " OFFSET $this->offset";
			}
		}
		return $return;
	}



	protected function buildTopClause()
	{
		if ($this->limit !== NULL && $this->driverName === 'dblib') {
			return " TOP ($this->limit)"; //! offset is not supported
		}
		return '';
	}



	protected function tryDelimite($s)
	{
		$driver = $this->driver;
		return preg_replace_callback('#(?<=[^\w`"\[]|^)[a-z_][a-z0-9_]*(?=[^\w`"(\]]|\z)#i', function($m) use ($driver) {
			return strtoupper($m[0]) === $m[0] ? $m[0] : $driver->delimite($m[0]);
		}, $s);
	}



	protected function removeExtraTables($expression)
	{
		return preg_replace('~(?:\\b[a-z_][a-z0-9_.:]*[.:])?([a-z_][a-z0-9_]*)[.:]([a-z_*])~i', '\\1.\\2', $expression); // rewrite tab1.tab2.col
	}

}
