<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 */

namespace Nette\Loaders;

use Nette;



/**
 * Limited scope for PHP code evaluation and script including.
 *
 * @author     David Grudl
 */
final class LimitedScope
{
	private static $vars;

	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new \LogicException("Cannot instantiate static class " . get_class($this));
	}



	/**
	 * Evaluates code in limited scope.
	 * @param  string  PHP code
	 * @param  array   local variables
	 * @return mixed   the return value of the evaluated code
	 */
	public static function evaluate(/*$code, array $vars = NULL*/)
	{
		if (func_num_args() > 1) {
			self::$vars = func_get_arg(1);
			extract(self::$vars);
		}
		return eval('?>' . func_get_arg(0));
	}



	/**
	 * Includes script in a limited scope.
	 * @param  string  file to include
	 * @param  array   local variables
	 * @return mixed   the return value of the included file
	 */
	public static function load(/*$file, array $vars = NULL*/)
	{
		if (func_num_args() > 1) {
			self::$vars = func_get_arg(1);
			extract(self::$vars);
		}
		return include func_get_arg(0);
	}

}