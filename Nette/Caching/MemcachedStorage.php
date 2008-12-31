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

require_once dirname(__FILE__) . '/../Caching/ICacheStorage.php';



/**
 * Memcached storage.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @package    Nette\Caching
 */
class MemcachedStorage extends /*Nette\*/Object implements ICacheStorage
{
	/**#@+ internal cache structure */
	const META_CONSTS = 'consts';
	const META_DATA = 'data';
	const META_DELTA = 'delta';
	const META_FILES = 'df';
	/**#@-*/

	/** @var Memcache */
	protected $memcache;

	/** @var string */
	protected $prefix;



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
		$this->memcache = new Memcache;
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
		//     df => array of dependent files (file => timestamp)
		//     consts => array of constants (const => [value])
		// )

		// verify dependencies
		if (!empty($meta[self::META_CONSTS])) {
			foreach ($meta[self::META_CONSTS] as $const => $value) {
				if (!defined($const) || constant($const) !== $value) {
					$this->memcache->delete($key);
					return NULL;
				}
			}
		}

		if (!empty($meta[self::META_FILES])) {
			//clearstatcache();
			foreach ($meta[self::META_FILES] as $depFile => $time) {
				if (@filemtime($depFile) <> $time) {
					$this->memcache->delete($key);
					return NULL;
				}
			}
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
	 * @return bool  TRUE if no problem
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
			if ($expire <= /*Nette\*/Tools::YEAR) {
				$expire += time();
			}
			if (!empty($dp[Cache::REFRESH])) {
				$meta[self::META_DELTA] = $expire - time(); // sliding time
			}
		}

		if (!empty($dp[Cache::FILES])) {
			//clearstatcache();
			foreach ((array) $dp[Cache::FILES] as $depFile) {
				$meta[self::META_FILES][$depFile] = @filemtime($depFile); // intentionally @
			}
		}

		if (!empty($dp[Cache::CONSTS])) {
			foreach ((array) $dp[Cache::CONSTS] as $const) {
				$meta[self::META_CONSTS][$const] = constant($const);
			}
		}

		return $this->memcache->set($this->prefix . $key, $meta, 0, $expire);
	}



	/**
	 * Removes item from the cache.
	 * @param  string key
	 * @return bool  TRUE if no problem
	 */
	public function remove($key)
	{
		return $this->memcache->delete($this->prefix . $key);
	}



	/**
	 * Removes items from the cache by conditions & garbage collector.
	 * @param  array  conditions
	 * @return bool  TRUE if no problem
	 */
	public function clean(array $conds)
	{
		if (!empty($conds[Cache::ALL])) {
			$this->memcache->flush();

		} elseif (isset($conds[Cache::TAGS]) || isset($conds[Cache::PRIORITY])) {
			throw new /*\*/NotSupportedException('Tags and priority is not supported by MemcachedStorage.');
		}

		return TRUE;
	}

}
