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
 * @package    Nette
 * @version    $Id$
 */


/**
 * json_encode in PHP < 5.2.0
 */
if (!function_exists('json_encode')) {

	/**
	 * JSON encode
	 *
	 * @author     David Grudl
	 * @copyright  Copyright (c) 2007 David Grudl
	 */
	function json_encode($val)
	{
		// indexed array
		if (is_array($val) && (!$val
			|| array_keys($val) === range(0, count($val) - 1))) {
			return '[' . implode(',', array_map('json_encode', $val)) . ']';
		}

		// associative array
		if (is_array($val) || is_object($val)) {
			$tmp = array();
			foreach ($val as $k => $v) {
				$tmp[] = json_encode((string) $k) . ':' . json_encode($v);
			}
			return '{' . implode(',', $tmp) . '}';
		}

		if (is_string($val)) {
			$val = str_replace(array("\\", "\x00"), array("\\\\", "\\u0000"), $val); // due to bug #40915
			return '"' . addcslashes($val, "\x8\x9\xA\xC\xD/\"") . '"';
		}

		if (is_int($val) || is_float($val)) {
			return rtrim(rtrim(number_format($val, 5, '.', ''), '0'), '.');
		}

		if (is_bool($val)) {
			return $val ? 'true' : 'false';
		}

		return 'null';
	}

}



/**
 * Fix for class::method callback in PHP < 5.2.2
 */
if (version_compare(PHP_VERSION , '5.2.2', '<')) {
	function fixCallback(& $callback)
	{
		if (is_string($callback) && strpos($callback, ':')) {
			$callback = explode('::', $callback);
		}
	}

} else {
	function fixCallback($foo) {}
}


/**
 * Fix for namespaced classes/interfaces in PHP < 5.3
 */
if (version_compare(PHP_VERSION , '5.3.0', '<')) {
	function fixNamespace(& $class)
	{
		if ($a = strrpos($class, '\\')) {
			$class = substr($class, $a + 1);
		}
	}

} else {
	function fixNamespace($foo) {}
}
