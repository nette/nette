<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

use Nette\Diagnostics\Debugger;


if (!function_exists('dump')) {
	/**
	 * Nette\Diagnostics\Debugger::dump() shortcut.
	 * @tracySkipLocation
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
class_alias('Nette\DI\CompilerExtension', 'Nette\Config\CompilerExtension');
class_alias('Nette\DI\Compiler', 'Nette\Config\Compiler');
