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
	const SLIDING = 'sliding';
	const TAGS = 'tags';
	const FILES = 'files';
	const ITEMS = 'items';
	const CONSTS = 'consts';
	const CALLBACKS = 'callbacks';
	const ALL = 'all';
	/**#@-*/

	/** @deprecated */
	const REFRESH = 'sliding';

	/** @internal */
	const NAMESPACE_SEPARATOR = "\x00";

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
		$this->namespace = (string) $namespace;

		if (strpos($this->namespace, self::NAMESPACE_SEPARATOR) !== FALSE) {
			throw new /*\*/InvalidArgumentException("Namespace name contains forbidden character.");
		}
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
	 * - Cache::PRIORITY => (int) priority
	 * - Cache::EXPIRE => (timestamp) expiration
	 * - Cache::SLIDING => (bool) use sliding expiration?
	 * - Cache::TAGS => (array) tags
	 * - Cache::FILES => (array|string) file names
	 * - Cache::ITEMS => (array|string) cache items
	 * - Cache::CONSTS => (array|string) cache items
	 *
	 * @param  string key
	 * @param  mixed  value
	 * @param  array  dependencies
	 * @return void
	 * @throws \InvalidArgumentException
	 */
	public function save($key, $data, array $dp = NULL)
	{
		if (!is_string($key)) {
			throw new /*\*/InvalidArgumentException("Cache key name must be string, " . gettype($key) ." given.");
		}

		// convert expire into relative amount of seconds
		if (!empty($dp[Cache::EXPIRE])) {
			$expire = & $dp[Cache::EXPIRE];
			if (is_string($expire) && !is_numeric($expire)) {
				$expire = strtotime($expire) - time();
			} elseif ($expire > /*Nette\*/Tools::YEAR) {
				$expire -= time();
			}
		}

		// convert FILES into CALLBACKS
		if (isset($dp[self::FILES])) {
			//clearstatcache();
			foreach ((array) $dp[self::FILES] as $item) {
				$dp[self::CALLBACKS][] = array(array(__CLASS__, 'checkFile'), $item, @filemtime($item)); // intentionally @
			}
			unset($dp[self::FILES]);
		}

		// add namespaces to items
		if (isset($dp[self::ITEMS])) {
			$dp[self::ITEMS] = (array) $dp[self::ITEMS];
			foreach ($dp[self::ITEMS] as $k => $item) {
				$dp[self::ITEMS][$k] = $this->namespace . self::NAMESPACE_SEPARATOR . $item;
			}
		}

		// convert CONSTS into CALLBACKS
		if (isset($dp[self::CONSTS])) {
			foreach ((array) $dp[self::CONSTS] as $item) {
				$dp[self::CALLBACKS][] = array(array(__CLASS__, 'checkConst'), $item, constant($item));
			}
			unset($dp[self::CONSTS]);
		}

		$this->key = NULL;
		$this->storage->write(
			$this->namespace . self::NAMESPACE_SEPARATOR . $key,
			$data,
			(array) $dp
		);
	}



	/**
	 * Removes items from the cache by conditions.
	 * Conditions are:
	 * - Cache::PRIORITY => (int) priority
	 * - Cache::TAGS => (array) tags
	 * - Cache::ALL => TRUE
	 *
	 * @param  array
	 * @return void
	 */
	public function clean(array $conds = NULL)
	{
		$this->storage->clean((array) $conds);
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
			$this->storage->remove($this->namespace . self::NAMESPACE_SEPARATOR . $key);
		} else {
			$this->storage->write($this->namespace . self::NAMESPACE_SEPARATOR . $key, $data, array());
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
		$this->data = $this->storage->read($this->namespace . self::NAMESPACE_SEPARATOR . $key);
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
		$this->data = $this->storage->read($this->namespace . self::NAMESPACE_SEPARATOR . $key);
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
		$this->storage->remove($this->namespace . self::NAMESPACE_SEPARATOR . $key);
	}



	/********************* dependency checkers ****************d*g**/



	/**
	 * Checks CALLBACKS dependencies.
	 * @param  array
	 * @return bool
	 */
	public static function checkCallbacks($callbacks)
	{
		foreach ($callbacks as $callback) {
			$func = array_shift($callback);
			if (!call_user_func_array($func, $callback)) {
				return FALSE;
			}
		}
		return TRUE;
	}



	/**
	 * Checks CONSTS dependency.
	 * @param  string
	 * @param  mixed
	 * @return bool
	 */
	private static function checkConst($const, $value)
	{
		return defined($const) && constant($const) === $value;
	}



	/**
	 * Checks FILES dependency.
	 * @param  string
	 * @param  int
	 * @return bool
	 */
	private static function checkFile($file, $time)
	{
		return @filemtime($file) == $time; // intentionally @
	}

}
