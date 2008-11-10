<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Loaders
 * @version    $Id$
 */

/*namespace Nette\Loaders;*/



require_once dirname(__FILE__) . '/../Object.php';



/**
 * Auto loader is responsible for loading classes and interfaces.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette\Loaders
 */
abstract class AutoLoader extends /*Nette\*/Object
{
	/** @var array  list of registered loaders */
	static private $loaders = array();

	/** @var int  for profiling purposes */
	static public $count = 0;



	/**
	 * Try to load the requested class.
	 * @param  string  class/interface name
	 * @return void
	 */
	final public static function load($type)
	{
		class_exists($type);
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
			throw new /*\*/RuntimeException('spl_autoload does not exist in this PHP installation.');
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



	/**
	 * Include script in a limited scope.
	 * @param  string  file to include
	 * @return mixed   the return value of the included file
	 */
	public static function includeOnce()
	{
		return include_once func_get_arg(0);
	}

}
