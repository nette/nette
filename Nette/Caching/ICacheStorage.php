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



/**
 * Cache storage (EXPERIMENTAL).
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Caching
 * @version    $Revision$ $Date$
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
	 * @return bool  TRUE if no problem
	 */
	function write($key, $data, array $dependencies);



	/**
	 * Removes item from the cache.
	 * @param  string key
	 * @return bool  TRUE if no problem
	 */
	function remove($key);



	/**
	 * Removes items from the cache by conditions.
	 * @param  array  conditions
	 * @return bool  TRUE if no problem
	 */
	function clean(array $conds);

}
