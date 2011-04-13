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



	/**
	 * Returns primary key value.
	 * @return string
	 */
	public function __toString()
	{
		return (string) $this[$this->table->primary]; // (string) - PostgreSQL returns int
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
	 * Returns referenced row.
	 * @param  string
	 * @return ActiveRow or NULL if the row does not exist
	 */
	public function ref($name)
	{
		$referenced = $this->table->getReferencedTable($name, $column);
		if (isset($referenced[$this[$column]])) { // referenced row may not exist
			$res = $referenced[$this[$column]];
			return $res;
		}
	}



	/**
	 * Returns referencing rows.
	 * @param  string table name
	 * @return GroupedSelection
	 */
	public function related($table)
	{
		$referencing = $this->table->getReferencingTable($table);
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
		if (array_key_exists($key, $this->data)) {
			$this->access($key);
			return $this->data[$key];
		}

		$column = $this->table->connection->databaseReflection->getReferencedColumn($key, $this->table->name);
		if (array_key_exists($column, $this->data)) {
			$value = $this->data[$column];
			$referenced = $this->table->getReferencedTable($key);
			$ret = isset($referenced[$value]) ? $referenced[$value] : NULL; // referenced row may not exist
			return $ret;
		}

		$this->access($key);
		if (array_key_exists($key, $this->data)) {
			return $this->data[$key];

		} else {
			$this->access($key, TRUE);

			$this->access($column);
			if (array_key_exists($column, $this->data)) {
				$value = $this->data[$column];
				$referenced = $this->table->getReferencedTable($key);
				$ret = isset($referenced[$value]) ? $referenced[$value] : NULL; // referenced row may not exist

			} else {
				$this->access($column, TRUE);
				trigger_error("Unknown column $key", E_USER_WARNING);
				$ret = NULL;
			}
			return $ret;
		}
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
		if ($this->table->connection->cache && $this->table->access($key, $delete)) {
			$this->data = $this->table[$this->data[$this->table->primary]]->data;
		}
	}

}
