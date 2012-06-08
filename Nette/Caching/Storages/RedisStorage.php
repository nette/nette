<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Caching\Storages;

use Nette,
	Nette\Caching\Cache;



/**
 * Memcached storage.
 *
 * TODO: Support for multiple Redis instances
 * TODO: I believe that "priorities", "callbacks" and "tag keys" (set of keys that has tag assigned)
 *	does not need more namespacing (which would require injecting namespace from Cache)
 *	because keys itself are namespaced. BUT namespacing would save data transfer
 * TODO: Check if all callbacks, items and other dependencies are deleted, when key is deleted
 *
 * @author     Ondřej Slámečka
 */
class RedisStorage extends Nette\Object implements Nette\Caching\IStorage
{
	/** @internal cache structure */
	const NAMESPACE_EXPIRATION = 'expiration',
		NAMESPACE_TAG_KEYS = 'tagkeys',
		NAMESPACE_ITEMS = 'di', // dependent items
		NAMESPACE_CALLBACKS = 'callbacks',
		KEY_PRIORITIES = 'priorities';

	/** @var \Redis */
	private $redis;



	/**
	 * Checks if Redis extension is available.
	 * @return bool
	 */
	public static function isAvailable()
	{
		return extension_loaded('redis');
	}



	public function __construct($host = 'localhost', $port = 6379)
	{
		if (!static::isAvailable()) {
			throw new Nette\NotSupportedException("PHP extension 'redis' is not loaded.");
		}

		$this->redis = new \Redis;
		$this->redis->connect($host, $port);
	}



	/**
	 * Read from cache.
	 * @param  string key
	 * @return mixed|NULL
	 */
	public function read($key)
	{
		$value = $this->redis->get($key) ?: NULL;

		$slidingExpirationKey = self::NAMESPACE_EXPIRATION . Cache::NAMESPACE_SEPARATOR . $key;
		$expiration = $this->redis->get($slidingExpirationKey);
		if ($expiration) {
			$this->redis->setTimeout($key, $expiration);
			$this->redis->setTimeout($slidingExpirationKey, $expiration);
		}

		$callbacks = $this->redis->sMembers(self::NAMESPACE_CALLBACKS . Cache::NAMESPACE_SEPARATOR . $key);
		if (!empty($callbacks)) {
			$callbacks = array_map('unserialize', $callbacks);

			if (!Cache::checkCallbacks($callbacks)) {
				$this->redis->delete($key, 0);
				return NULL;
			}
		}

		return $value;
	}



	/**
	 * Prevents item reading and writing. Lock is released by write() or remove().
	 * @param  string key
	 * @return void
	 */
	public function lock($key)
	{
		// TODO: http://redis.io/topics/transactions ?
	}



	/**
	 * Writes item into the cache.
	 * @param  string key
	 * @param  mixed  data
	 * @param  array  dependencies
	 * @return void
	 */
	public function write($key, $data, array $dependencies)
	{
		$this->redis->set($key, $data);

		if (isset($dependencies[Cache::ITEMS])) {
			foreach ($dependencies[Cache::ITEMS] as $item) {
				$this->redis->sAdd(self::NAMESPACE_ITEMS . Cache::NAMESPACE_SEPARATOR . $item, $key);
			}
		}

		if ($dependentItems = $this->redis->sMembers(self::NAMESPACE_ITEMS . Cache::NAMESPACE_SEPARATOR . $key)) {
			$this->redis->delete($dependentItems);
		}

		if (isset($dependencies[Cache::TAGS])) {
			$dependencies[Cache::TAGS] = (array) $dependencies[Cache::TAGS];

			foreach ($dependencies[Cache::TAGS] as $tag) {
				$this->redis->sAdd(self::NAMESPACE_TAG_KEYS . Cache::NAMESPACE_SEPARATOR . $tag, $key);
			}
		}

		if (isset($dependencies[Cache::CALLBACKS])) {
			foreach ($dependencies[Cache::CALLBACKS] as $cb) {
				$this->redis->sAdd(self::NAMESPACE_CALLBACKS . Cache::NAMESPACE_SEPARATOR . $key, serialize($cb));
			}
		}

		if (isset($dependencies[Cache::PRIORITY])) {
			$this->redis->zAdd(self::KEY_PRIORITIES, $dependencies[Cache::PRIORITY], $key);
		}

		if (isset($dependencies[Cache::EXPIRATION])) {
			$this->redis->setTimeout($key, $dependencies[Cache::EXPIRATION]);

			if (isset($dependencies[Cache::SLIDING])) {
				$slidingExpirationKey = self::NAMESPACE_EXPIRATION . Cache::NAMESPACE_SEPARATOR . $key;
				$this->redis->set($slidingExpirationKey, $dependencies[Cache::EXPIRATION]);
				$this->redis->setTimeout($slidingExpirationKey, $dependencies[Cache::EXPIRATION]);
			}
		}
	}



	/**
	 * Removes item from the cache.
	 * @param  string key
	 * @return void
	 */
	public function remove($key)
	{
		$this->redis->delete($key);

		if ($dependentItems = $this->redis->sMembers(self::NAMESPACE_ITEMS . Cache::NAMESPACE_SEPARATOR . $key)) {
			$this->redis->delete($dependentItems);
		}
	}



	/**
	 * Removes items from the cache by conditions.
	 * @param  array  conditions
	 * @return void
	 */
	public function clean(array $conds)
	{
		if (!empty($conds[Cache::ALL])) {
			$this->redis->flushDB();
		} else {
			$keys = array();

			if (isset($conds[Cache::PRIORITY])) {

				$byPriority = $this->redis->zRangeByScore(self::KEY_PRIORITIES, 0, $conds[Cache::PRIORITY]);
				self::arrayAppend($keys, $byPriority);				

				$this->redis->zDeleteRangeByScore(self::KEY_PRIORITIES, 0, $conds[Cache::PRIORITY]);
			}

			if (isset($conds[Cache::TAGS])) {
				$conds[Cache::TAGS] = (array) $conds[Cache::TAGS];

				foreach($conds[Cache::TAGS] as $tag) {
					$byTags = $this->redis->sMembers(self::NAMESPACE_TAG_KEYS . Cache::NAMESPACE_SEPARATOR . $tag);
					self::arrayAppend($keys, $byTags);
				}
			}

			$this->redis->delete($keys);

			// Remove keys keeping information about expiration time
			$expirationKeys = array_map(function($key) {
				return self::NAMESPACE_EXPIRATION . Cache::NAMESPACE_SEPARATOR . $key;
			}, $keys);

			$this->redis->delete($expirationKeys);

			// Remove all dependent items
			foreach ($keys as $key) {
				if ($dependentItems = $this->redis->get(self::NAMESPACE_ITEMS . Cache::NAMESPACE_SEPARATOR . $key)) {
					$this->delete($dependentItems);
				}
			}
		}
	}



	/**
	* Append $append to $array.
	* This function is much faster then $array = array_merge($array, $append)
	* Source: http://api.nette.org/2.0/source-Caching.Storages.FileJournal.php.html#1166
	* @param  array
	* @param  array
	* @return void
	*/
	private static function arrayAppend(array &$array, array $append)
	{
		foreach ($append as $value) {
			$array[] = $value;
		}
	}

}
