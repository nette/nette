<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette\Test
 */



/**
 * Asseratation test helpers.
 *
 * @author     David Grudl
 * @package    Nette\Test
 */
class Assert
{

	/**
	 * Checks assertation.
	 * @param  mixed  expected
	 * @param  mixed  actual
	 * @return void
	 */
	public static function same($expected, $actual)
	{
		if ($actual !== $expected) {
			self::note('Failed asserting that ' . self::dump($actual) . ' is not identical to ' . self::dump($expected));
		}
	}



	/**
	 * Checks assertation.
	 * @param  mixed  expected
	 * @param  mixed  actual
	 * @return void
	 */
	public static function equal($expected, $actual)
	{
		if ($actual != $expected) {
			self::note('Failed asserting that ' . self::dump($actual) . ' is not equal to ' . self::dump($expected));
		}
	}



	/**
	 * Checks TRUE assertation.
	 * @param  mixed  actual
	 * @return void
	 */
	public static function true($actual)
	{
		if ($actual !== TRUE) {
			self::note('Failed asserting that ' . self::dump($actual) . ' is not TRUE');
		}
	}



	/**
	 * Checks FALSE assertation.
	 * @param  mixed  actual
	 * @return void
	 */
	public static function false($actual)
	{
		if ($actual !== FALSE) {
			self::note('Failed asserting that ' . self::dump($actual) . ' is not FALSE');
		}
	}



	/**
	 * Checks NULL assertation.
	 * @param  mixed  actual
	 * @return void
	 */
	public static function null($actual)
	{
		if ($actual !== NULL) {
			self::note('Failed asserting that ' . self::dump($actual) . ' is not NULL');
		}
	}



	/**
	 * Checks exception assertation.
	 * @param  string class
	 * @param  string message
	 * @param  Exception
	 * @return void
	 */
	public static function exception($class, $message, $actual)
	{
		if (!($actual instanceof $class)) {
			self::note('Failed asserting that ' . get_class($actual) . " is class $class");
		}
		if ($message) {
			self::match($message, $actual->getMessage());
		}
	}



	/**
	 * Failed assertion
	 * @return void
	 */
	public static function failed()
	{
		self::note('Failed asserting');
	}



	/**
	 * Compares results.
	 * @param  string
	 * @param  string
	 * @return bool
	 */
	public static function match($expected, $actual)
	{
		$expected = rtrim(preg_replace("#[\t ]+\n#", "\n", str_replace("\r\n", "\n", $expected)));
		$actual = rtrim(preg_replace("#[\t ]+\n#", "\n", str_replace("\r\n", "\n", $actual)));

		$re = strtr($expected, array(
			'%a%' => '[^\r\n]+',    // one or more of anything except the end of line characters
			'%a?%'=> '[^\r\n]*',    // zero or more of anything except the end of line characters
			'%A%' => '.+',          // one or more of anything including the end of line characters
			'%A?%'=> '.*',          // zero or more of anything including the end of line characters
			'%s%' => '[\t ]+',      // one or more white space characters except the end of line characters
			'%s?%'=> '[\t ]*',      // zero or more white space characters except the end of line characters
			'%S%' => '\S+',         // one or more of characters except the white space
			'%S?%'=> '\S*',         // zero or more of characters except the white space
			'%c%' => '[^\r\n]',     // a single character of any sort (except the end of line)
			'%d%' => '[0-9]+',      // one or more digits
			'%d?%'=> '[0-9]*',      // zero or more digits
			'%i%' => '[+-]?[0-9]+', // signed integer value
			'%f%' => '[+-]?\.?\d+\.?\d*(?:[Ee][+-]?\d+)?', // floating point number
			'%h%' => '[0-9a-fA-F]+',// one or more HEX digits
			'%ns%'=> '(?:[_0-9a-zA-Z\\\\]+\\\\|N)?',// PHP namespace
			'%[^' => '[^',          // reg-exp
			'%['  => '[',           // reg-exp
			']%'  => ']+',          // reg-exp

			'.' => '\.', '\\' => '\\\\', '+' => '\+', '*' => '\*', '?' => '\?', '[' => '\[', '^' => '\^', ']' => '\]', '$' => '\$', '(' => '\(', ')' => '\)', // preg quote
			'{' => '\{', '}' => '\}', '=' => '\=', '!' => '\!', '>' => '\>', '<' => '\<', '|' => '\|', ':' => '\:', '-' => '\-', "\x00" => '\000', '#' => '\#', // preg quote
		));

		$res = preg_match("#^$re$#s", $actual);
		if ($res === FALSE || preg_last_error()) {
			throw new Exception("Error while executing regular expression.");
		}
		if (!$res) {
			self::note('Failed asserting that ' . self::dump($actual) . ' is not identical to ' . self::dump($expected));
		}
	}



	/**
	 * Dumps information about a variable in readable format.
	 * @param  mixed  variable to dump
	 * @return void
	 */
	private static function dump($var)
	{
		if (is_bool($var)) {
			return $var ? 'TRUE' : 'FALSE';

		} elseif ($var === NULL) {
			return "NULL";

		} elseif (is_int($var)) {
			return "$var";

		} elseif (is_float($var)) {
			return "$var";

		} elseif (is_string($var)) {
			return var_export($var, TRUE);

		} elseif (is_array($var)) {
			return "array(" . count($var) . ")";

		} elseif ($var instanceof Exception) {
			return 'Exception ' . get_class($var) . ': ' . ($var->getCode() ? '#' . $var->getCode() . ' ' : '') . $var->getMessage();

		} elseif (is_object($var)) {
			$arr = (array) $var;
			return "object(" . get_class($var) . ") (" . count($arr) . ")";

		} elseif (is_resource($var)) {
			return "resource(" . get_resource_type($var) . ")";

		} else {
			return "unknown type";
		}
	}



	/**
	 * Returns message and file and line from call stack.
	 * @param  string
	 * @return void
	 */
	private static function note($message)
	{
		echo $message;
		$trace = debug_backtrace();
		$trace = end($trace);
		if (isset($trace['file'], $trace['line'])) {
			echo ' in file ' . $trace['file'] . ' on line ' . $trace['line'];
		}
		echo "\n\n";
	}

}
