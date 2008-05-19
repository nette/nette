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
 * @package    Nette::Caching
 */

/*namespace Nette::Caching;*/



require_once dirname(__FILE__) . '/../Object.php';

require_once dirname(__FILE__) . '/../Caching/ICacheStorage.php';



/**
 * Memcached storage.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Caching
 * @version    $Revision$ $Date$
 */
class MemcachedStorage extends /*Nette::*/Object implements ICacheStorage
{
	/** @var Memcache */
	protected $memcache;

	/** @var string */
	protected $prefix;



	public function __construct($host = 'localhost', $port = 11211, $prefix = '')
	{
		if (!extension_loaded('memcache')) {
			throw new /*::*/Exception("PHP extension 'memcache' is not loaded.");
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
		// )

		// verify dependencies
		if (!empty($meta['df'])) {
			clearstatcache();
			foreach ($meta['df'] as $depFile => $time) {
				if (@filemtime($depFile) <> $time) {
					$this->memcache->delete($key);
					return NULL;
				}
			}
		}

		if (!empty($meta['delta'])) {
			$this->memcache->replace($key, $meta, 0, $meta['delta'] + time());
		}

		return $meta['data'];
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
		if (!empty($dp['tags']) || isset($dp['priority']) || !empty($dp['items'])) {
			throw new /*::*/NotSupportedException('Tags, priority and dependent items are not supported by MemcachedStorage.');
		}

		$meta = array(
			'data' => $data,
		);
		$expire = 0;

		if (!empty($dp['expire'])) {
			$expire = (int) $dp['expire']; // absolute time
			if (!empty($dp['refresh'])) {
				$meta['delta'] = $expire - time(); // sliding time
			}
		}

		if (!empty($dp['files'])) {
			clearstatcache();
			foreach ((array) $dp['files'] as $depFile) {
				$meta['df'][$depFile] = @filemtime($depFile); // intentionally @
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
		if (!empty($conds['all'])) {
			$this->memcache->flush();

		} elseif (isset($conds['tags']) || isset($conds['priority'])) {
			throw new /*::*/NotSupportedException('Tags and priority is not supported by MemcachedStorage.');
		}

		return TRUE;
	}

}
