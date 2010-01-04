<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Loaders
 */

/*namespace Nette\Loaders;*/



/**
 * Auto loader is responsible for loading classes and interfaces.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Loaders
 */
class SimpleLoader extends AutoLoader
{

	/**
	 * Handles autoloading of classes or interfaces.
	 * @param  string
	 * @return void
	 */
	public function tryLoad($type)
	{
		if (strpbrk($type, './;|') !== FALSE) {
			throw new /*\*/InvalidArgumentException("Invalid class/interface name '$type'.");
		}

		$file = strtr($type, '\\', '/') . '.php';

		/*
		if (strncmp($type, 'Nette\\', 6) === 0) {
			$file = dirname(dirname(dirname(__FILE__))) . '/' . $file;
		}
		*/

		@LimitedScope::load($file);
		self::$count++;
	}

}
