<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

use Nette\Diagnostics\Debugger;


if (!function_exists('dump')) {
	/**
	 * Nette\Diagnostics\Debugger::dump() shortcut.
	 */
	function dump($var)
	{
		foreach (func_get_args() as $arg) {
			Debugger::dump($arg);
		}
		return $var;
	}
}


if (!function_exists('callback')) {
	/**
	 * Nette\Callback factory.
	 * @param  mixed   class, object, callable
	 * @param  string  method
	 * @return Nette\Callback
	 */
	function callback($callback, $m = NULL)
	{
		return new Nette\Callback($callback, $m);
	}
}

class_alias('Nette\Configurator', 'Nette\Config\Configurator');
class_alias('Nette\Config\CompilerExtension', 'Nette\DI\CompilerExtension');
class_alias('Nette\Config\Compiler', 'Nette\DI\Compiler');
