<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2009 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Loaders
 * @version    $Id$
 */

/*namespace Nette\Loaders;*/



/**
 * Limited scope for PHP code evaluation and script including.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @package    Nette\Loaders
 */
final class LimitedScope
{

	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new /*\*/LogicException("Cannot instantiate static class " . get_class($this));
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
			$__vars = func_get_arg(1);
			extract($__vars, EXTR_SKIP);
			unset($__vars);
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
			$__vars = func_get_arg(1);
			extract($__vars, EXTR_SKIP);
			unset($__vars);
		}
		return include func_get_arg(0);
	}

}