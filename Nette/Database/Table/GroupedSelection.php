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

use Nette;



/**
 * Representation of filtered table grouped by some column.
 * Selector is based on the great library NotORM http://www.notorm.com written by Jakub Vrana.
 *
 * @author     Jakub Vrana
 */
class GroupedSelection extends Selection
{
	/** @var Selection referenced table */
	private $refTable;

	/** @var string grouping column name */
	private $column;

	/** @var string */
	private $delimitedColumn;

	/** @var */
	public $active;



	public function __construct($name, Selection $refTable, $column)
	{
		parent::__construct($name, $refTable->connection);
		$this->refTable = $refTable;
		$this->through($column);
	}



	/**
	 * Specify referencing column.
	 * @param  string
	 * @return GroupedSelection provides a fluent interface
	 */
	public function through($column)
	{
		$this->column = $column;
		$this->delimitedColumn = $this->refTable->connection->getSupplementalDriver()->delimite($this->column);
		return $this;
	}



	public function select($columns)
	{
		if (!$this->select) {
			$this->select[] = "$this->delimitedName.$this->delimitedColumn";
		}
		return parent::select($columns);
	}



	public function order($columns)
	{
		if (!$this->order) { // improve index utilization
			$this->order[] = "$this->delimitedName.$this->delimitedColumn"
				. (preg_match('~\\bDESC$~i', $columns) ? ' DESC' : '');
		}
		return parent::order($columns);
	}



	public function aggregation($function)
	{
		$join = $this->createJoins(implode(',', $this->conditions), TRUE) + $this->createJoins($function);
		$column = ($join ? "$this->table." : '') . $this->column;
		$query = "SELECT $function, $this->delimitedColumn FROM $this->delimitedName" . implode($join);
		if ($this->where) {
			$query .= ' WHERE (' . implode(') AND (', $this->where) . ')';
		}
		$query .= " GROUP BY $this->delimitedColumn";
		$aggregation = & $this->refTable->aggregation[$query];
		if ($aggregation === NULL) {
			$aggregation = array();
			foreach ($this->query($query, $this->parameters) as $row) {
				$aggregation[$row[$this->column]] = $row;
			}
		}

		foreach ($aggregation[$this->active] as $val) {
			return $val;
		}
	}



	public function insert($data)
	{
		if ($data instanceof \Traversable && !$data instanceof Selection) {
			$data = iterator_to_array($data);
		}
		if (is_array($data)) {
			$data[$this->column] = $this->active;
		}
		return parent::insert($data);
	}



	public function update($data)
	{
		$where = $this->where;
		$this->where[0] = "$this->delimitedColumn = " . $this->connection->quote($this->active);
		$return = parent::update($data);
		$this->where = $where;
		return $return;
	}



	public function delete()
	{
		$where = $this->where;
		$this->where[0] = "$this->delimitedColumn = " . $this->connection->quote($this->active);
		$return = parent::delete();
		$this->where = $where;
		return $return;
	}



	protected function execute()
	{
		if ($this->rows !== NULL) {
			return;
		}

		$referencing = & $this->refTable->referencing[$this->getSql()];
		if ($referencing === NULL) {
			$limit = $this->limit;
			$rows = count($this->refTable->rows);
			if ($this->limit && $rows > 1) {
				$this->limit = NULL;
			}
			parent::execute();
			$this->limit = $limit;
			$referencing = array();
			$offset = array();
			foreach ($this->rows as $key => $row) {
				$ref = & $referencing[$row[$this->column]];
				$skip = & $offset[$row[$this->column]];
				if ($limit === NULL || $rows <= 1 || (count($ref) < $limit && $skip >= $this->offset)) {
					$ref[$key] = $row;
				} else {
					unset($this->rows[$key]);
				}
				$skip++;
				unset($ref, $skip);
			}
		}

		$this->data = & $referencing[$this->active];
		if ($this->data === NULL) {
			$this->data = array();
		}
	}

}
