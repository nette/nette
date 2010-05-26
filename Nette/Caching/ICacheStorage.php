<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette\Caching
 */

namespace Nette\Caching;

use Nette;



/**
 * Cache storage.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Caching
 */
interface ICacheStorage
{

	/**
	 * Read from cache.
	 * @param  string key
	 * @return mixed|NULL
	 */
	function read($key);

	/**
	 * Writes item into the cache.
	 * @param  string key
	 * @param  mixed  data
	 * @param  array  dependencies
	 * @return void
	 */
	function write($key, $data, array $dependencies);

	/**
	 * Removes item from the cache.
	 * @param  string key
	 * @return void
	 */
	function remove($key);

	/**
	 * Removes items from the cache by conditions.
	 * @param  array  conditions
	 * @return void
	 */
	function clean(array $conds);

}
