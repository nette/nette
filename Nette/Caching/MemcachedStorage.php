<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 */

namespace Nette\Caching;

use Nette;



/**
 * Memcached storage.
 *
 * @author     David Grudl
 */
class MemcachedStorage extends Nette\Object implements ICacheStorage
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

	/** @var Nette\Context */
	private $context;



	/**
	 * Checks if Memcached extension is available.
	 * @return bool
	 */
	public static function isAvailable()
	{
		return extension_loaded('memcache');
	}



	public function __construct($host = 'localhost', $port = 11211, $prefix = '', Nette\Context $context = NULL)
	{
		if (!self::isAvailable()) {
			throw new \NotSupportedException("PHP extension 'memcache' is not loaded.");
		}

		$this->prefix = $prefix;
		$this->context = $context;
		$this->memcache = new \Memcache;
		Nette\Debug::tryError();
		$this->memcache->connect($host, $port);
		if (Nette\Debug::catchError($msg)) {
			throw new \InvalidStateException($msg);
		}
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
			$this->memcache->delete($key, 0);
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
		if (!empty($dp[Cache::ITEMS])) {
			throw new \NotSupportedException('Dependent items are not supported by MemcachedStorage.');
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

		if (!empty($dp[Cache::TAGS]) || isset($dp[Cache::PRIORITY])) {
			if (!$this->context) {
				throw new \InvalidStateException('CacheJournal has not been provided.');
			}
			$this->getJournal()->write($this->prefix . $key, $dp);
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
		$this->memcache->delete($this->prefix . $key, 0);
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

		} elseif ($this->context) {
			foreach ($this->getJournal()->clean($conds) as $entry) {
				$this->memcache->delete($entry, 0);
			}
		}
	}



	/**
	 * @return ICacheJournal
	 */
	protected function getJournal()
	{
		return $this->context->getService('Nette\\Caching\\ICacheJournal');
	}

}
