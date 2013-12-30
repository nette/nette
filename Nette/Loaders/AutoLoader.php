<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Loaders;

use Nette;


/**
 * Auto loader is responsible for loading classes and interfaces.
 *
 * @author     David Grudl
 * @deprecated
 */
abstract class AutoLoader extends Nette\Object
{
	/** @var array  list of registered loaders */
	static private $loaders = array();


	/**
	 * Try to load the requested class.
	 * @param  string  class/interface name
	 * @return void
	 */
	public static function load($type)
	{
		trigger_error(__METHOD__ . '() is deprecated.', E_USER_DEPRECATED);
		foreach (func_get_args() as $type) {
			if (!class_exists($type)) {
				throw new Nette\InvalidStateException("Unable to load class or interface '$type'.");
			}
		}
	}


	/**
	 * Return all registered autoloaders.
	 * @return AutoLoader[]
	 */
	public static function getLoaders()
	{
		trigger_error(__METHOD__ . '() is deprecated.', E_USER_DEPRECATED);
		return array_values(self::$loaders);
	}


	/**
	 * Register autoloader.
	 * @param  bool  prepend autoloader?
	 * @return void
	 */
	public function register($prepend = FALSE)
	{
		if (!function_exists('spl_autoload_register')) {
			throw new Nette\NotSupportedException('spl_autoload does not exist in this PHP installation.');
		}

		spl_autoload_register(array($this, 'tryLoad'), TRUE, (bool) $prepend);
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
