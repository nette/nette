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
 * Memory cache storage.
 *
 * @author     David Grudl
 */
class MemoryStorage extends Nette\Object implements ICacheStorage
{
	/** @var array */
	private $data = array();



	/**
	 * Read from cache.
	 * @param  string key
	 * @return mixed|NULL
	 */
	public function read($key)
	{
		return isset($this->data[$key]) ? $this->data[$key] : NULL;
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
		$this->data[$key] = $data;
	}



	/**
	 * Removes item from the cache.
	 * @param  string key
	 * @return void
	 */
	public function remove($key)
	{
		unset($this->data[$key]);
	}



	/**
	 * Removes items from the cache by conditions & garbage collector.
	 * @param  array  conditions
	 * @return void
	 */
	public function clean(array $conds)
	{
		if (!empty($conds[Cache::ALL])) {
			$this->data = array();
		}
	}

}
