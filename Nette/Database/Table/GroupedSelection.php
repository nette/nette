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
		$this->sqlBuilder = new SqlBuilder($this);
		$this->active = $active;
		return $this;
	}



	/** @deprecated */
	public function through($column)
	{
		trigger_error(__METHOD__ . '() is deprecated; use ' . __CLASS__ . '::related("' . $this->name . '", "' . $column . '") instead.', E_USER_DEPRECATED);
		$this->column = $column;
		$this->delimitedColumn = $this->refTable->connection->getSupplementalDriver()->delimite($this->column);
		return $this;
	}



	public function select($columns)
	{
		if (!$this->sqlBuilder->getSelect()) {
			$this->sqlBuilder->select("$this->name.$this->column");
		}

		return parent::select($columns);
	}



	public function order($columns)
	{
		if (!$this->sqlBuilder->getOrder()) {
			// improve index utilization
			$this->sqlBuilder->order("$this->name.$this->column" . (preg_match('~\\bDESC$~i', $columns) ? ' DESC' : ''));
		}

		return parent::order($columns);
	}



	/********************* aggregations ****************d*g**/



	public function aggregation($function)
	{
		$aggregation = & $this->aggregation[$function . $this->sqlBuilder->getSql() . json_encode($this->sqlBuilder->getParameters())];

		if ($aggregation === NULL) {
			$aggregation = array();

			$selection = $this->createSelectionInstance();
			$selection->getSqlBuilder()->importConditions($this->getSqlBuilder());
			$selection->select($function);
			$selection->select("$this->name.$this->column");
			$selection->group("$this->name.$this->column");

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



	public function count($column = NULL)
	{
		$return = parent::count($column);
		return isset($return) ? $return : 0;
	}



	/********************* internal ****************d*g**/



	protected function execute()
	{
		if ($this->rows !== NULL) {
			return;
		}

		$hash = md5($this->sqlBuilder->getSql() . json_encode($this->sqlBuilder->getParameters()));
		$referencing = & $this->refTable->referencing[$hash];

		$this->rows = & $referencing['rows'];
		$this->referenced = & $referencing['refs'];
		$refData = & $referencing['data'];

		if ($refData === NULL) {
			$limit = $this->sqlBuilder->getLimit();
			$rows = count($this->refTable->rows);
			if ($limit && $rows > 1) {
				$this->sqlBuilder->limit(NULL, NULL);
			}
			parent::execute();
			$this->sqlBuilder->limit($limit, NULL);
			$refData = array();
			$offset = array();
			foreach ($this->rows as $key => $row) {
				$ref = & $refData[$row[$this->column]];
				$skip = & $offset[$row[$this->column]];
				if ($limit === NULL || $rows <= 1 || (count($ref) < $limit && $skip >= $this->sqlBuilder->getOffset())) {
					$ref[$key] = $row;
				} else {
					unset($this->rows[$key]);
				}
				$skip++;
				unset($ref, $skip);
			}
		}

		$this->data = & $refData[$this->active];
		if ($this->data === NULL) {
			$this->data = array();
		} else {
			reset($this->data);
		}
	}



	/********************* manipulation ****************d*g**/



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
		$builder = $this->sqlBuilder;

		$this->sqlBuilder = new SqlBuilder($this);
		$this->where($this->column, $this->active);
		$return = parent::update($data);

		$this->sqlBuilder = $builder;
		return $return;
	}



	public function delete()
	{
		$builder = $this->sqlBuilder;

		$this->sqlBuilder = new SqlBuilder($this);
		$this->where($this->column, $this->active);
		$return = parent::delete();

		$this->sqlBuilder = $builder;
		return $return;
	}

}
