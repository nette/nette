<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

// no namespace



/**
 * Nette\Callback factory.
 * @param  mixed   class, object, function, callback
 * @param  string  method
 * @return Nette\Callback
 */
function callback($callback, $m = NULL)
{
	return ($m === NULL && $callback instanceof Nette\Callback) ? $callback : new Nette\Callback($callback, $m);
}



/**
 * Nette\Debug::dump shortcut.
 */
function dump($var)
{
	foreach (func_get_args() as $arg) Nette\Debug::dump($arg);
	return $var;
}
