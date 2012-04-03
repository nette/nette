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

use Nette;



/**
 * Representation of filtered table grouped by some column.
 * GroupedSelection is based on the great library NotORM http://www.notorm.com written by Jakub Vrana.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 */
class GroupedSelection extends Selection
{
	/** @var Selection referenced table */
	protected $refTable;

	/** @var string grouping column name */
	protected $column;

	/** @var string */
	protected $delimitedColumn;

	/** @var int primary key */
	protected $active;

	/** @var array of referencing cached results */
	protected $referencing;

	/** @var array of [conditions => [key => ActiveRow]] */
	protected $aggregation = array();



	public function __construct($name, Selection $refTable, $column)
	{
		parent::__construct($name, $refTable->connection);
		$this->refTable = $refTable;
		$this->column = $column;
		$this->delimitedColumn = $this->connection->getSupplementalDriver()->delimite($this->column);
	}



	/**
	 * @internal
	 * @param  int  $active
	 * @return GroupedSelection
	 */
	public function setActive($active)
	{
		$this->rows = NULL;
		$this->active = $active;
		$this->select = $this->where = $this->conditions = $this->parameters = $this->order = array();
		$this->limit = $this->offset = NULL;
		$this->group = $this->having = '';
		return $this;
	}



	/** @deprecated */
	public function through($column)
	{
		trigger_error(__METHOD__ . '() is deprecated; use ' . __CLASS__ . '::related("' . $this->name . '", "' . $column . '") instead.', E_USER_WARNING);
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
		$aggregation = & $this->aggregation[$function . implode('', $this->where) . implode('', $this->conditions)];
		if ($aggregation === NULL) {
			$aggregation = array();

			$selection = new Selection($this->name, $this->connection);
			$selection->where = $this->where;
			$selection->parameters = $this->parameters;
			$selection->conditions = $this->conditions;

			$selection->select($function);
			$selection->select("{$this->name}.{$this->column}");
			$selection->group("{$this->name}.{$this->column}");

			foreach ($selection as $row) {
				$aggregation[$row[$this->column]] = $row;
			}
		}

		if (isset($aggregation[$this->active])) {
			foreach ($aggregation[$this->active] as $val) {
				return $val;
			}
		}
	}



	public function count($column = '')
	{
		$return = parent::count($column);
		return isset($return) ? $return : 0;
	}



	public function insert($data)
	{
		if ($data instanceof \Traversable && !$data instanceof Selection) {
			$data = iterator_to_array($data);
		}

		if (Nette\Utils\Validators::isList($data)) {
			foreach (array_keys($data) as $key) {
				$data[$key][$this->column] = $this->active;
			}
		} else {
			$data[$this->column] = $this->active;
		}

		return parent::insert($data);
	}



	public function update($data)
	{
		$condition = array($this->where, $this->parameters);

		$this->where[0] = "$this->delimitedColumn = ?";
		$this->parameters[0] = $this->active;
		$return = parent::update($data);

		list($this->where, $this->parameters) = $condition;
		return $return;
	}



	public function delete()
	{
		$condition = array($this->where, $this->parameters);

		$this->where[0] = "$this->delimitedColumn = ?";
		$this->parameters[0] = $this->active;
		$return = parent::delete();

		list($this->where, $this->parameters) = $condition;
		return $return;
	}



	protected function execute()
	{
		if ($this->rows !== NULL) {
			return;
		}

		$hash = md5($this->getSql() . json_encode($this->parameters));
		$referencing = & $this->referencing[$hash];
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
		} else {
			reset($this->data);
		}
	}

}
