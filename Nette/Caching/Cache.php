<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2009 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Caching
 * @version    $Id$
 */

/*namespace Nette\Caching;*/



require_once dirname(__FILE__) . '/../Object.php';



/**
 * Implements the cache for a application.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @package    Nette\Caching
 */
class Cache extends /*Nette\*/Object implements /*\*/ArrayAccess
{
	/**#@+ dependency */
	const PRIORITY = 'priority';
	const EXPIRE = 'expire';
	const REFRESH = 'refresh';
	const TAGS = 'tags';
	const FILES = 'files';
	const ITEMS = 'items';
	const CONSTS = 'consts';
	const ALL = 'all';
	/**#@-*/

	/** @var ICacheStorage */
	private $storage;

	/** @var string */
	private $namespace;

	/** @var string  last query cache */
	private $key;

	/** @var mixed  last query cache */
	private $data;



	public function __construct(ICacheStorage $storage, $namespace = NULL)
	{
		$this->storage = $storage;
		$this->namespace = $namespace == NULL ? '' : $namespace . "\x00";
	}



	/**
	 * Returns cache storage.
	 * @return ICacheStorage
	 */
	public function getStorage()
	{
		return $this->storage;
	}



	/**
	 * Returns cache namespace.
	 * @return string
	 */
	public function getNamespace()
	{
		return $this->namespace;
	}



	/**
	 * Discards the internal cache.
	 * @return void
	 */
	public function release()
	{
		$this->key = $this->data = NULL;
	}



	/**
	 * Writes item into the cache.
	 * Dependencies are:
	 *       priority => (int) priority
	 *       expire => (timestamp) expiration
	 *       refresh => (bool) use sliding expiration?
	 *       tags => (array) tags
	 *       files => (array|string) file names
	 *       items => (array|string) cache items
	 *       consts => (array|string) cache items
	 *
	 * @param  string key
	 * @param  mixed
	 * @param  array
	 * @return void
	 * @throws \InvalidArgumentException
	 */
	public function save($key, $data, array $dependencies = NULL)
	{
		if (!is_string($key)) {
			throw new /*\*/InvalidArgumentException("Cache key name must be string, " . gettype($key) ." given.");
		}

		$this->key = NULL;

		$this->adjust($dependencies);

		$this->storage->write(
			$this->namespace . $key,
			$data,
			$dependencies
		);
	}



	/**
	 * Removes items from the cache by conditions.
	 * @param  array
	 * @return void
	 */
	public function clean(array $conds = NULL)
	{
		$this->adjust($conds);
		$this->storage->clean($conds);
	}



	/**
	 * Update dependencies array.
	 * @param  array
	 * @return void
	 */
	private function adjust(& $arr)
	{
		if ($arr === NULL) {
			$arr = array();
		}

		if (isset($arr[self::TAGS])) {
			$arr[self::TAGS] = (array) $arr[self::TAGS];
			foreach ($arr[self::TAGS] as $key => $value) {
				$arr[self::TAGS][$key] = $this->namespace . $value;
			}
		}

		if (isset($arr[self::ITEMS])) {
			$arr[self::ITEMS] = (array) $arr[self::ITEMS];
			foreach ($arr[self::ITEMS] as $key => $value) {
				$arr[self::ITEMS][$key] = $this->namespace . $value;
			}
		}
	}



	/********************* interface \ArrayAccess ****************d*g**/



	/**
	 * Inserts (replaces) item into the cache (\ArrayAccess implementation).
	 * @param  string key
	 * @param  mixed
	 * @return void
	 * @throws \InvalidArgumentException
	 */
	public function offsetSet($key, $data)
	{
		if (!is_string($key)) { // prevents NULL
			throw new /*\*/InvalidArgumentException("Cache key name must be string, " . gettype($key) ." given.");
		}

		$this->key = $this->data = NULL;
		if ($data === NULL) {
			$this->storage->remove($this->namespace . $key);
		} else {
			$this->storage->write($this->namespace . $key, $data, array());
		}
	}



	/**
	 * Retrieves the specified item from the cache or NULL if the key is not found (\ArrayAccess implementation).
	 * @param  string key
	 * @return mixed|NULL
	 * @throws \InvalidArgumentException
	 */
	public function offsetGet($key)
	{
		if (!is_string($key)) {
			throw new /*\*/InvalidArgumentException("Cache key name must be string, " . gettype($key) ." given.");
		}

		if ($this->key === $key) {
			return $this->data;
		}
		$this->key = $key;
		$this->data = $this->storage->read($this->namespace . $key);
		return $this->data;
	}



	/**
	 * Exists item in cache? (\ArrayAccess implementation).
	 * @param  string key
	 * @return bool
	 * @throws \InvalidArgumentException
	 */
	public function offsetExists($key)
	{
		if (!is_string($key)) {
			throw new /*\*/InvalidArgumentException("Cache key name must be string, " . gettype($key) ." given.");
		}

		$this->key = $key;
		$this->data = $this->storage->read($this->namespace . $key);
		return $this->data !== NULL;
	}



	/**
	 * Removes the specified item from the cache.
	 * @param  string key
	 * @return void
	 * @throws \InvalidArgumentException
	 */
	public function offsetUnset($key)
	{
		if (!is_string($key)) {
			throw new /*\*/InvalidArgumentException("Cache key name must be string, " . gettype($key) ." given.");
		}

		$this->key = $this->data = NULL;
		$this->storage->remove($this->namespace . $key);
	}

}
