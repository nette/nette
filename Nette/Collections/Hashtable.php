<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette\Collections
 */

/*namespace Nette\Collections;*/



/**
 * The exception that is thrown when the key specified for accessing
 * an element in a collection does not match any key.
 * @package    Nette\Collections
 */
class KeyNotFoundException extends /*\*/RuntimeException
{
}



/**
 * Provides the base class for a generic collection of keys and values.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Collections
 */
class Hashtable extends Collection implements IMap
{
	/** @var bool */
	private $throwKeyNotFound = FALSE;



	/**
	 * Inserts the specified element to the map.
	 * @param  mixed
	 * @param  mixed
	 * @return bool
	 * @throws \InvalidArgumentException, \InvalidStateException
	 */
	public function add($key, $item)
	{
		// note: $item is nullable to be compatible with that of ICollection::add()
		if (!is_scalar($key)) {
			throw new /*\*/InvalidArgumentException("Key must be either a string or an integer, " . gettype($key) ." given.");
		}

		if (parent::offsetExists($key)) {
			throw new /*\*/InvalidStateException('An element with the same key already exists.');
		}

		$this->beforeAdd($item);
		parent::offsetSet($key, $item);
		return TRUE;
	}



	/**
	 * Append is not supported.
	 */
	public function append($item)
	{
		throw new /*\*/NotSupportedException;
	}



	/**
	 * Returns a array of the keys contained in this map.
	 * return array
	 */
	public function getKeys()
	{
		return array_keys($this->getArrayCopy());
	}



	/**
	 * Returns the key of the first occurrence of the specified element,.
	 * or FALSE if this map does not contain this element.
	 * @param  mixed
	 * @return mixed
	 */
	public function search($item)
	{
		return array_search($item, $this->getArrayCopy(), TRUE);
	}



	/**
	 * Import from array or any traversable object.
	 * @param  array|\Traversable
	 * @return void
	 * @throws \InvalidArgumentException
	 */
	public function import($arr)
	{
		$this->updating();

		if (!(is_array($arr) || $arr instanceof /*\*/Traversable)) {
			throw new /*\*/InvalidArgumentException("Argument must be traversable.");
		}

		if ($this->getItemType() === NULL) { // optimalization
			$this->setArray((array) $arr);

		} else {
			$this->clear();
			foreach ($arr as $key => $item) {
				$this->offsetSet($key, $item);
			}
		}
	}



	/**
	 * Returns variable or $default if there is no element.
	 * @param  string key
	 * @param  mixed  default value
	 * @return mixed
	 * @throws \InvalidArgumentException
	 */
	public function get($key, $default = NULL)
	{
		if (!is_scalar($key)) {
			throw new /*\*/InvalidArgumentException("Key must be either a string or an integer, " . gettype($key) ." given.");
		}

		if (parent::offsetExists($key)) {
			return parent::offsetGet($key);

		} else {
			return $default;
		}
	}



	/**
	 * Do throw KeyNotFoundException?
	 * @return void
	 */
	public function throwKeyNotFound($val = TRUE)
	{
		$this->throwKeyNotFound = (bool) $val;
	}



	/********************* interface \ArrayAccess ****************d*g**/



	/**
	 * Inserts (replaces) item (\ArrayAccess implementation).
	 * @param  string key
	 * @param  object
	 * @return void
	 * @throws \NotSupportedException, \InvalidArgumentException
	 */
	public function offsetSet($key, $item)
	{
		if (!is_scalar($key)) { // prevents NULL
			throw new /*\*/InvalidArgumentException("Key must be either a string or an integer, " . gettype($key) ." given.");
		}

		$this->beforeAdd($item);
		parent::offsetSet($key, $item);
	}



	/**
	 * Returns item (\ArrayAccess implementation).
	 * @param  string key
	 * @return mixed
	 * @throws KeyNotFoundException, \InvalidArgumentException
	 */
	public function offsetGet($key)
	{
		if (!is_scalar($key)) {
			throw new /*\*/InvalidArgumentException("Key must be either a string or an integer, " . gettype($key) ." given.");
		}

		if (parent::offsetExists($key)) {
			return parent::offsetGet($key);

		} elseif ($this->throwKeyNotFound) {
			throw new KeyNotFoundException;

		} else {
			return NULL;
		}
	}



	/**
	 * Exists item? (\ArrayAccess implementation).
	 * @param  string key
	 * @return bool
	 * @throws \InvalidArgumentException
	 */
	public function offsetExists($key)
	{
		if (!is_scalar($key)) {
			throw new /*\*/InvalidArgumentException("Key must be either a string or an integer, " . gettype($key) ." given.");
		}

		return parent::offsetExists($key);
	}



	/**
	 * Removes the element at the specified position in this list.
	 * @param  string key
	 * @return void
	 * @throws \NotSupportedException, \InvalidArgumentException
	 */
	public function offsetUnset($key)
	{
		$this->updating();

		if (!is_scalar($key)) {
			throw new /*\*/InvalidArgumentException("Key must be either a string or an integer, " . gettype($key) ." given.");
		}

		if (parent::offsetExists($key)) {
			parent::offsetUnset($key);
		}
	}

}