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
 * Single row representation.
 * Selector is based on the great library NotORM http://www.notorm.com written by Jakub Vrana.
 *
 * @author     Jakub Vrana
 */
class ActiveRow extends Nette\Object implements \IteratorAggregate, \ArrayAccess
{
	/** @var Selection */
	protected $table;

	/** @var array of row data */
	protected $data;

	/** @var array of new values {@see ActiveRow::update()} */
	private $modified = array();



	public function __construct(array $data, Selection $table)
	{
		$this->data = $data;
		$this->table = $table;
	}



	public function __toString()
	{
		try {
			return (string) $this->getPrimary();
		} catch (\Exception $e) {
			Nette\Diagnostics\Debugger::toStringException($e);
		}
	}



	/**
	 * @return array
	 */
	public function toArray()
	{
		$this->access(NULL);
		return $this->data;
	}



	/**
	 * Returns primary key value.
	 * @return mixed
	 */
	public function getPrimary()
	{
		if (!isset($this->data[$this->table->primary])) {
			throw new Nette\NotSupportedException("Table {$this->table->name} does not have any primary key.");
		}
		return $this[$this->table->primary];
	}



	/**
	 * Returns referenced row.
	 * @param  string
	 * @param  string
	 * @return ActiveRow or NULL if the row does not exist
	 */
	public function ref($key, $throughColumn = NULL)
	{
		list($table, $column) = $this->table->connection->databaseReflection->getBelongsToReference($this->table->name, $key);
		$column = $throughColumn ?: $column;
		return $this->getReference($table, $column);
	}



	/**
	 * Returns referencing rows.
	 * @param  string
	 * @param  string
	 * @return GroupedSelection
	 */
	public function related($key, $throughColumn = NULL)
	{
		if (strpos($key, '.') !== FALSE) {
			list($key, $throughColumn) = explode('.', $key);
		}

		list($table, $column) = $this->table->connection->databaseReflection->getHasManyReference($this->table->name, $key);
		$column = $throughColumn ?: $column;
		$referencing = $this->table->getReferencingTable($table, $column);
		$referencing->active = $this[$this->table->primary];
		return $referencing;
	}



	/**
	 * Updates row.
	 * @param  array or NULL for all modified values
	 * @return int number of affected rows or FALSE in case of an error
	 */
	public function update($data = NULL)
	{
		if ($data === NULL) {
			$data = $this->modified;
		}
		return $this->table->connection->table($this->table->name)
			->where($this->table->primary, $this[$this->table->primary])
			->update($data);
	}



	/**
	 * Deletes row.
	 * @return int number of affected rows or FALSE in case of an error
	 */
	public function delete()
	{
		return $this->table->connection->table($this->table->name)
			->where($this->table->primary, $this[$this->table->primary])
			->delete();
	}



	/********************* interface IteratorAggregate ****************d*g**/



	public function getIterator()
	{
		$this->access(NULL);
		return new \ArrayIterator($this->data);
	}



	/********************* interface ArrayAccess & magic accessors ****************d*g**/



	/**
	 * Stores value in column.
	 * @param  string column name
	 * @return NULL
	 */
	public function offsetSet($key, $value)
	{
		$this->__set($key, $value);
	}



	/**
	 * Returns value of column.
	 * @param  string column name
	 * @return string
	 */
	public function offsetGet($key)
	{
		return $this->__get($key);
	}



	/**
	 * Tests if column exists.
	 * @param  string column name
	 * @return bool
	 */
	public function offsetExists($key)
	{
		return $this->__isset($key);
	}



	/**
	 * Removes column from data.
	 * @param  string column name
	 * @return NULL
	 */
	public function offsetUnset($key)
	{
		$this->__unset($key);
	}



	public function __set($key, $value)
	{
		$this->data[$key] = $value;
		$this->modified[$key] = $value;
	}



	public function &__get($key)
	{
		$this->access($key);
		if (array_key_exists($key, $this->data)) {
			return $this->data[$key];
		}

		list($table, $column) = $this->table->connection->databaseReflection->getBelongsToReference($this->table->name, $key);
		$referenced = $this->getReference($table, $column);

		if (!$referenced) {
			$this->access($key, TRUE);
		}

		return $referenced;
	}



	public function __isset($key)
	{
		$this->access($key);
		$return = array_key_exists($key, $this->data);
		if (!$return) {
			$this->access($key, TRUE);
		}
		return $return;
	}



	public function __unset($key)
	{
		unset($this->data[$key]);
		unset($this->modified[$key]);
	}



	public function access($key, $delete = FALSE)
	{
		if ($this->table->connection->getCache() && !isset($this->modified[$key]) && $this->table->access($key, $delete)) {
			$id = (isset($this->data[$this->table->primary]) ? $this->data[$this->table->primary] : $this->data);
			$this->data = $this->table[$id]->data;
		}
	}



	protected function getReference($table, $column)
	{
		if (array_key_exists($column, $this->data)) {
			$this->access($column);

			$value = $this->data[$column];
			$value = $value instanceof ActiveRow ? $value->getPrimary() : $value;

			$referenced = $this->table->getReferencedTable($table, $column, !empty($this->modified[$column]));
			$referenced = isset($referenced[$value]) ? $referenced[$value] : NULL; // referenced row may not exist

			if (!empty($this->modified[$column])) { // cause saving changed column and prevent regenerating referenced table for $column
				$this->modified[$column] = 0; // 0 fails on empty, pass on isset
			}

			return $referenced;
		}
	}

}
