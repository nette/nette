<?php

if (!function_exists('json_encode')) { // since PHP 5.2.0

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
			$val = addslashes($val); // ' " \ NUL - due to bug #40915
			return '"' . addcslashes($val, "\x8..\xA\xC\xD/") . '"';
		}

		if (is_int($val) || is_float($val)) {
			return (string) $val;
		}

		if (is_bool($val)) {
			return $val ? 'true' : 'false';
		}

		return 'null';
	}

}
