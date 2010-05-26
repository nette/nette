<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette\Loaders
 */

namespace Nette\Loaders;

use Nette;



/**
 * Auto loader is responsible for loading classes and interfaces.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Loaders
 */
abstract class AutoLoader extends Nette\Object
{
	/** @var array  list of registered loaders */
	static private $loaders = array();

	/** @var int  for profiling purposes */
	public static $count = 0;



	/**
	 * Try to load the requested class.
	 * @param  string  class/interface name
	 * @return void
	 */
	final public static function load($type)
	{
		foreach (func_get_args() as $type) {
			if (!class_exists($type)) {
				throw new \InvalidStateException("Unable to load class or interface '$type'.");
			}
		}
	}



	/**
	 * Return all registered autoloaders.
	 * @return array of AutoLoader
	 */
	final public static function getLoaders()
	{
		return array_values(self::$loaders);
	}



	/**
	 * Register autoloader.
	 * @return void
	 */
	public function register()
	{
		if (!function_exists('spl_autoload_register')) {
			throw new \RuntimeException('spl_autoload does not exist in this PHP installation.');
		}

		spl_autoload_register(array($this, 'tryLoad'));
		self::$loaders[spl_object_hash($this)] = $this;
	}



	/**
	 * Unregister autoloader.
	 * @return bool
	 */
	public function unregister()
	{
		unset(self::$loaders[spl_object_hash($this)]);
		return spl_autoload_unregister(array($this, 'tryLoad'));
	}



	/**
	 * Handles autoloading of classes or interfaces.
	 * @param  string
	 * @return void
	 */
	abstract public function tryLoad($type);

}
