<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Utils;

use Nette;


/**
 * JSON encoder and decoder.
 *
 * @author     David Grudl
 */
class Json
{
	const FORCE_ARRAY = 1;

	/** @var array */
	private static $messages = array(
		JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
		JSON_ERROR_STATE_MISMATCH => 'Syntax error, malformed JSON',
		JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
		JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON',
		5 /*JSON_ERROR_UTF8*/ => 'Invalid UTF-8 sequence',
		6 /*PHP_JSON_ERROR_RECURSION*/ => 'Recursion detected',
		7 /*PHP_JSON_ERROR_INF_OR_NAN*/ => 'Inf and NaN cannot be JSON encoded',
		8 /*PHP_JSON_ERROR_UNSUPPORTED_TYPE*/ => 'Type is not supported',
	);


	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new Nette\StaticClassException;
	}


	/**
	 * Returns the JSON representation of a value.
	 * @param  mixed
	 * @return string
	 */
	public static function encode($value)
	{
		if (function_exists('ini_set')) { // workaround for PHP bugs #52397, #54109, #63004
			$old = ini_set('display_errors', 0); // needed to receive 'Invalid UTF-8 sequence' error
		}
		set_error_handler(function($severity, $message) { // needed to receive 'recursion detected' error
			restore_error_handler();
			throw new JsonException($message);
		});
		$json = json_encode($value);
		restore_error_handler();
		if (isset($old)) {
			ini_set('display_errors', $old);
		}
		if (PHP_VERSION_ID >= 50300 && ($error = json_last_error())) {
			throw new JsonException(isset(static::$messages[$error]) ? static::$messages[$error] : 'Unknown error', $error);
		}
		$json = str_replace(array("\xe2\x80\xa8", "\xe2\x80\xa9"), array('\u2028', '\u2029'), $json);
		return $json;
	}


	/**
	 * Decodes a JSON string.
	 * @param  string
	 * @param  int
	 * @return mixed
	 */
	public static function decode($json, $options = 0)
	{
		$json = (string) $json;
		$value = json_decode($json, (bool) ($options & self::FORCE_ARRAY));
		if ($value === NULL && $json !== '' && strcasecmp($json, 'null')) { // '' do not clean json_last_error
			$error = PHP_VERSION_ID >= 50300 ? json_last_error() : 0;
			throw new JsonException(isset(static::$messages[$error]) ? static::$messages[$error] : 'Unknown error', $error);
		}
		return $value;
	}

}


/**
 * The exception that indicates error of JSON encoding/decoding.
 */
class JsonException extends \Exception
{
}
