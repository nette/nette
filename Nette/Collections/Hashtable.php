<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com/
 *
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com/
 * @category   Nette
 * @package    Nette::Collections
 */

/*namespace Nette::Collections;*/



require_once dirname(__FILE__) . '/../Collections/Collection.php';

require_once dirname(__FILE__) . '/../Collections/IMap.php';



/**
 * The exception that is thrown when the key specified for accessing
 * an element in a collection does not match any key.
 * @package    Nette::Collections
 */
class KeyNotFoundException extends /*::*/RuntimeException
{
}



/**
 * Provides the base class for a generic collection of keys and values.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Collections
 * @version    $Revision$ $Date$
 */
class Hashtable extends Collection implements IMap
{
	/** @var bool */
	protected $strict = TRUE;



	/**
	 * Inserts the specified element to the map.
	 * @param  mixed
	 * @param  mixed
	 * @return bool
	 * @throws ::InvalidArgumentException, ::InvalidStateException
	 */
	public function add($key, $item = NULL)
	{
		// note: $item is nullable to be compatible with that of ICollection::add()
		if (!is_scalar($key)) {
			throw new /*::*/InvalidArgumentException('Key must be either a string or an integer.');
		}

		if (array_key_exists($key, $this->data)) {
			throw new /*::*/InvalidStateException('An element with the same key already exists.');
		}

		$this->beforeAdd($item);
		$this->data[$key] = $item;
		return TRUE;
	}



	/**
	 * Returns a array of the keys contained in this map.
	 * return array
	 */
	public function getKeys()
	{
		return array_keys($this->data);
	}



	/**
	 * Returns the key of the first occurrence of the specified element,.
	 * or FALSE if this map does not contain this element.
	 * @param  mixed
	 * @return mixed
	 */
	public function search($item)
	{
		return array_search($item, $this->data, TRUE);
	}



	/**
	 * Import from array or any traversable object.
	 * @param  array|Traversable
	 * @return void
	 * @throws ::InvalidArgumentException
	 */
	public function import($arr)
	{
		if (is_array($arr) || $arr instanceof Traversable) {
			foreach ($arr as $key => $item) {
				$this->beforeAdd($item);
				$this->data[$key] = $item;
			}
		} else {
			throw new /*::*/InvalidArgumentException("Argument must be traversable.");
		}
	}



	/**
	 * Returns variable or $default if there is no element.
	 * @param  string key
	 * @return mixed
	 * @throws ::InvalidArgumentException
	 */
	public function get($key, $default = NULL)
	{
		if (!is_scalar($key)) {
			throw new /*::*/InvalidArgumentException('Key must be either a string or an integer.');
		}

		if (array_key_exists($key, $this->data)) {
			return $this->data[$key];
		} else {
			return $default;
		}
	}



	/********************* interface ::ArrayAccess ****************d*g**/



	/**
	 * Inserts (replaces) item (::ArrayAccess implementation).
	 * @param  string key
	 * @param  object
	 * @return void
	 * @throws ::NotSupportedException, ::InvalidArgumentException
	 */
	public function offsetSet($key, $item)
	{
		if (!is_scalar($key)) { // prevents NULL
			throw new /*::*/InvalidArgumentException('Key must be either a string or an integer.');
		}

		$this->beforeAdd($item);
		$this->data[$key] = $item;
	}



	/**
	 * Returns item (::ArrayAccess implementation).
	 * @param  string key
	 * @return mixed
	 * @throws KeyNotFoundException, ::InvalidArgumentException
	 */
	public function offsetGet($key)
	{
		if (!is_scalar($key)) {
			throw new /*::*/InvalidArgumentException('Key must be either a string or an integer.');
		}

		if (array_key_exists($key, $this->data)) {
			return $this->data[$key];
		}

		if ($this->strict) {
			throw new KeyNotFoundException;
		} else {
			return NULL;
		}
	}



	/**
	 * Exists item? (::ArrayAccess implementation).
	 * @param  string key
	 * @return bool
	 * @throws ::InvalidArgumentException
	 */
	public function offsetExists($key)
	{
		if (!is_scalar($key)) {
			throw new /*::*/InvalidArgumentException('Key must be either a string or an integer.');
		}

		return array_key_exists($key, $this->data);
	}



	/**
	 * Removes the element at the specified position in this list.
	 * @param  string key
	 * @return void
	 * @throws ::NotSupportedException, ::InvalidArgumentException
	 */
	public function offsetUnset($key)
	{
		if (!is_scalar($key)) {
			throw new /*::*/InvalidArgumentException('Key must be either a string or an integer.');
		}

		$this->beforeRemove();
		unset($this->data[$key]);
	}

}