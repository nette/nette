<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette
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
