<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Database;

use Nette;



/**
 * Represents a single table row.
 *
 * @author     Jan Skrasek
 */
class Row extends Nette\Object implements \IteratorAggregate, \ArrayAccess
{
	/** @var array of row data */
	protected $data = array();



	public function __construct()
	{
	}



	public function __set($key, $value)
	{
		$this->data[$key] = $value;
	}



	public function &__get($key)
	{
		if (array_key_exists($key, $this->data)) {
			return $this->data[$key];
		}

		throw new Nette\MemberAccessException("Cannot read an undeclared column \"$key\".");
	}



	public function __isset($key)
	{
		return isset($this->data[$key]);
	}



	public function __unset($key)
	{
		unset($this->data[$key]);
	}



	public function getIterator()
	{
		return new \ArrayIterator($this->data);
	}



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
		if (is_int($key)) {
			$arr = array_values($this->data);
			return $arr[$key];
		}

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

}
