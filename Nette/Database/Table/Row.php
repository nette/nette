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
	Nette\Database\Reflection\MissingReferenceException;



/**
 * Single row representation.
 * ActiveRow is based on the great library NotORM http://www.notorm.com written by Jakub Vrana.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 */
class Row implements \IteratorAggregate, IRow
{
	/** @var BaseResultSet */
	protected $table;

	/** @var array of row data */
	protected $data;



	public function __construct(array $data, BaseResultSet $table)
	{
		$this->data = $data;
		$this->setTable($table);
	}



	/**
	 * @internal
	 * @ignore
	 */
	public function setTable($resultSet)
	{
		if (!$resultSet instanceof BaseResultSet) {
			throw new \LogicException('Row table must be descendant of Nette\Database\Table\BaseResultSet.');
		}
		$this->table = $resultSet;
	}



	/**
	 * @internal
	 */
	public function getTable()
	{
		return $this->table;
	}



	public function __toString()
	{
		try {
			return (string) $this->getPrimary();
		} catch (\Exception $e) {
			trigger_error("Exception in " . __METHOD__ . "(): {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}", E_USER_ERROR);
		}
	}



	/**
	 * @return array
	 */
	public function toArray()
	{
		return $this->data;
	}



	/**
	 * Returns primary key value.
	 * @param  bool
	 * @return mixed
	 */
	public function getPrimary($need = TRUE)
	{
		$primary = $this->table->getPrimary();
		if (!is_array($primary)) {
			if (isset($this->data[$primary])) {
				return $this->data[$primary];
			} elseif ($need) {
				throw new Nette\InvalidStateException("Row does not contain primary $primary column data.");
			} else {
				return NULL;
			}
		} else {
			$primaryVal = array();
			foreach ($primary as $key) {
				if (!isset($this->data[$key])) {
					if ($need) {
						throw new Nette\InvalidStateException("Row does not contain primary $key column data.");
					} else {
						return NULL;
					}
				}
				$primaryVal[$key] = $this->data[$key];
			}
			return $primaryVal;
		}
	}



	/**
	 * Returns row signature (composition of primary keys)
	 * @param  bool
	 * @return string
	 */
	public function getSignature($need = TRUE)
	{
		return implode('|', (array) $this->getPrimary($need));
	}



	/**
	 * Returns referenced row.
	 * @param  string
	 * @param  string
	 * @return IRow or NULL if the row does not exist
	 */
	public function ref($key, $throughColumn = NULL)
	{
		if (!$throughColumn) {
			list($key, $throughColumn) = $this->table->getDatabaseReflection()->getBelongsToReference($this->table->getName(), $key);
		}

		return $this->getReference($key, $throughColumn);
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
		} elseif (!$throughColumn) {
			list($key, $throughColumn) = $this->table->getDatabaseReflection()->getHasManyReference($this->table->getName(), $key);
		}

		return $this->table->getReferencingTable($key, $throughColumn, $this[$this->table->getPrimary()]);
	}




	/********************* interface IteratorAggregate ****************d*g**/



	public function getIterator()
	{
		return new \ArrayIterator($this->data);
	}



	/********************* interface ArrayAccess & magic accessors ****************d*g**/



	/**
	 * Stores value in column.
	 * @param  string column name
	 * @param  string value
	 * @return void
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
	 * @return void
	 */
	public function offsetUnset($key)
	{
		$this->__unset($key);
	}



	public function __set($key, $value)
	{
		throw new Nette\DeprecatedException('ActiveRow is read-only; use update() method instead.');
	}



	public function &__get($key)
	{
		if (array_key_exists($key, $this->data)) {
			return $this->data[$key];
		}

		try {
			list($table, $column) = $this->table->getDatabaseReflection()->getBelongsToReference($this->table->getName(), $key);
			$referenced = $this->getReference($table, $column);
			if ($referenced !== FALSE) {
				return $referenced;
			}
		} catch(MissingReferenceException $e) {}

		throw new Nette\MemberAccessException("Cannot read an undeclared column \"$key\".");
	}



	public function __isset($key)
	{
		if (array_key_exists($key, $this->data)) {
			return isset($this->data[$key]);
		}
		return FALSE;
	}



	public function __unset($key)
	{
		throw new Nette\DeprecatedException('ActiveRow is read-only.');
	}



	protected function getReference($table, $column)
	{
		if (array_key_exists($column, $this->data)) {
			$value = $this->data[$column];
			$referenced = $this->table->getReferencedTable($table, $column, $value);
			return isset($referenced[$value]) ? $referenced[$value] : NULL; // referenced row may not exist
		}

		return FALSE;
	}

}
