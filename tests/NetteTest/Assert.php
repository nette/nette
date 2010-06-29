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
		if (isset($trace[1]['file'], $trace[1]['line'])) {
			echo ' in file ' . $trace[1]['file'] . ' on line ' . $trace[1]['line'];
		}
		echo "\n\n";
	}

}
