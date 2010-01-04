<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Caching
 */

/*namespace Nette\Caching;*/



/**
 * Memcached storage.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Caching
 */
class MemcachedStorage extends /*Nette\*/Object implements ICacheStorage
{
	/**#@+ @internal cache structure */
	const META_CALLBACKS = 'callbacks';
	const META_DATA = 'data';
	const META_DELTA = 'delta';
	/**#@-*/

	/** @var Memcache */
	private $memcache;

	/** @var string */
	private $prefix;



	/**
	 * Checks if Memcached extension is available.
	 * @return bool
	 */
	public static function isAvailable()
	{
		return extension_loaded('memcache');
	}



	public function __construct($host = 'localhost', $port = 11211, $prefix = '')
	{
		if (!self::isAvailable()) {
			throw new /*\*/Exception("PHP extension 'memcache' is not loaded.");
		}

		$this->prefix = $prefix;
		$this->memcache = new /*\*/Memcache;
		$this->memcache->connect($host, $port);
	}



	/**
	 * Read from cache.
	 * @param  string key
	 * @return mixed|NULL
	 */
	public function read($key)
	{
		$key = $this->prefix . $key;
		$meta = $this->memcache->get($key);
		if (!$meta) return NULL;

		// meta structure:
		// array(
		//     data => stored data
		//     delta => relative (sliding) expiration
		//     callbacks => array of callbacks (function, args)
		// )

		// verify dependencies
		if (!empty($meta[self::META_CALLBACKS]) && !Cache::checkCallbacks($meta[self::META_CALLBACKS])) {
			$this->memcache->delete($key);
			return NULL;
		}

		if (!empty($meta[self::META_DELTA])) {
			$this->memcache->replace($key, $meta, 0, $meta[self::META_DELTA] + time());
		}

		return $meta[self::META_DATA];
	}



	/**
	 * Writes item into the cache.
	 * @param  string key
	 * @param  mixed  data
	 * @param  array  dependencies
	 * @return void
	 */
	public function write($key, $data, array $dp)
	{
		if (!empty($dp[Cache::TAGS]) || isset($dp[Cache::PRIORITY]) || !empty($dp[Cache::ITEMS])) {
			throw new /*\*/NotSupportedException('Tags, priority and dependent items are not supported by MemcachedStorage.');
		}

		$meta = array(
			self::META_DATA => $data,
		);

		$expire = 0;
		if (!empty($dp[Cache::EXPIRE])) {
			$expire = (int) $dp[Cache::EXPIRE];
			if (!empty($dp[Cache::SLIDING])) {
				$meta[self::META_DELTA] = $expire; // sliding time
			}
		}

		if (!empty($dp[Cache::CALLBACKS])) {
			$meta[self::META_CALLBACKS] = $dp[Cache::CALLBACKS];
		}

		$this->memcache->set($this->prefix . $key, $meta, 0, $expire);
	}



	/**
	 * Removes item from the cache.
	 * @param  string key
	 * @return void
	 */
	public function remove($key)
	{
		$this->memcache->delete($this->prefix . $key);
	}



	/**
	 * Removes items from the cache by conditions & garbage collector.
	 * @param  array  conditions
	 * @return void
	 */
	public function clean(array $conds)
	{
		if (!empty($conds[Cache::ALL])) {
			$this->memcache->flush();

		} elseif (isset($conds[Cache::TAGS]) || isset($conds[Cache::PRIORITY])) {
			throw new /*\*/NotSupportedException('Tags and priority is not supported by MemcachedStorage.');
		}
	}

}
