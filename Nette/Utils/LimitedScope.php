<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Utils;

use Nette;


/**
 * Limited scope for PHP code evaluation and script including.
 *
 * @deprecated
 */
class LimitedScope
{

	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new Nette\StaticClassException;
	}


	/**
	 * Evaluates code in limited scope.
	 * @param  string  PHP code
	 * @param  array   local variables
	 * @return mixed   the return value of the evaluated code
	 */
	public static function evaluate(/*$code, array $vars = NULL*/)
	{
		trigger_error(__METHOD__ . '() is deprecated.', E_USER_DEPRECATED);
		if (func_num_args() > 1) {
			foreach (func_get_arg(1) as $__k => $__v) $$__k = $__v;
			unset($__k, $__v);
		}
		$res = eval('?>' . func_get_arg(0));
		if ($res === FALSE && ($error = error_get_last()) && $error['type'] === E_PARSE) {
			throw new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
		}
		return $res;
	}


	/**
	 * Includes script in a limited scope.
	 * @param  string  file to include
	 * @param  array   local variables
	 * @return mixed   the return value of the included file
	 */
	public static function load(/*$file, array $vars = NULL*/)
	{
		trigger_error(__METHOD__ . '() is deprecated.', E_USER_DEPRECATED);
		if (func_num_args() > 1 && is_array(func_get_arg(1))) {
			foreach (func_get_arg(1) as $__k => $__v) $$__k = $__v;
			unset($__k, $__v);
		}
		return include func_get_arg(0);
	}

}
