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
 * @package    Nette\Test
 */



/**
 * Test helpers.
 *
 * @author     David Grudl
 * @package    Nette\Test
 */
final class NetteTestHelpers
{
	/** @var int */
	static public $maxDepth = 5;

	/** @var array */
	private static $sections;



	/**
	 * Configures PHP and environment.
	 * @return void
	 */
	public static function startup()
	{
		error_reporting(E_ALL | E_STRICT);
		ini_set('display_errors', TRUE);
		ini_set('html_errors', FALSE);
		ini_set('log_errors', FALSE);


		$_SERVER = array_intersect_key($_SERVER, array_flip(array('PHP_SELF', 'SCRIPT_NAME', 'SERVER_ADDR', 'SERVER_SOFTWARE', 'HTTP_HOST', 'DOCUMENT_ROOT', 'OS')));
		$_SERVER['REQUEST_TIME'] = 1234567890;
		$_ENV = array();

		if (PHP_SAPI !== 'cli') {
			header('Content-Type: text/plain; charset=utf-8');
		}

		if (extension_loaded('xdebug')) {
			xdebug_disable();
			xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);
			register_shutdown_function(array(__CLASS__, 'prepareSaveCoverage'));
		}

		set_exception_handler(array(__CLASS__, 'exceptionHandler'));
	}



	/**
	 * Purges directory.
	 * @param  string
	 * @return void
	 */
	public static function purge($dir)
	{
		@mkdir($dir); // intentionally @
		foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::CHILD_FIRST) as $entry) {
			if ($entry->getBasename() === '.gitignore') {
				// ignore
			} elseif ($entry->isDir()) {
				rmdir($entry);
			} else {
				unlink($entry);
			}
		}
	}



	/**
	 * Returns current test section.
	 * @param  string
	 * @param  string
	 * @return mixed
	 */
	public static function getSection($file, $section)
	{
		if (!isset(self::$sections[$file])) {
			self::$sections[$file] = NetteTestCase::parseSections($file);
		}

		$lowerSection = strtolower($section);
		if (!isset(self::$sections[$file][$lowerSection])) {
			throw new Exception("Missing section '$section' in file '$file'.");
		}

		if (in_array($section, array('GET', 'POST', 'SERVER'), TRUE)) {
			return NetteTestCase::parseLines(self::$sections[$file][$lowerSection], '=');
		} else {
			return self::$sections[$file][$lowerSection];
		}
	}



	/**
	 * Dumps information about a variable in readable format.
	 * @param  mixed  variable to dump
	 * @return void
	 * @internal
	 */
	public static function dump(& $var, $level = 0)
	{
		static $tableUtf, $tableBin, $re = '#[^\x09\x0A\x0D\x20-\x7E\xA0-\x{10FFFF}]#u';
		if ($tableUtf === NULL) {
			foreach (range("\x00", "\xFF") as $ch) {
				if (ord($ch) < 32 && strpos("\r\n\t", $ch) === FALSE) $tableUtf[$ch] = $tableBin[$ch] = '\\x' . str_pad(dechex(ord($ch)), 2, '0', STR_PAD_LEFT);
				elseif (ord($ch) < 127) $tableUtf[$ch] = $tableBin[$ch] = $ch;
				else { $tableUtf[$ch] = $ch; $tableBin[$ch] = '\\x' . dechex(ord($ch)); }
			}
			$tableUtf['\\x'] = $tableBin['\\x'] = '\\\\x';
		}

		if (is_bool($var)) {
			echo "bool(" . ($var ? 'TRUE' : 'FALSE') . ")\n";

		} elseif ($var === NULL) {
			echo "NULL\n";

		} elseif (is_int($var)) {
			echo "int($var)\n";

		} elseif (is_float($var)) {
			echo "float($var)\n";

		} elseif (is_string($var)) {
			$s = strtr($var, preg_match($re, $var) || preg_last_error() ? $tableBin : $tableUtf);
			echo "string(" . strlen($var) . ") \"$s\"\n";

		} elseif (is_array($var)) {
			echo "array(" . count($var) . ") ";
			$space = str_repeat("\t", $level);

			static $marker;
			if ($marker === NULL) $marker = uniqid("\x00", TRUE);
			if (empty($var)) {

			} elseif (isset($var[$marker])) {
				echo "{\n$space\t*RECURSION*\n$space}";

			} elseif ($level < self::$maxDepth) {
				echo "{\n";
				$var[$marker] = 0;
				foreach ($var as $k => &$v) {
					if ($k === $marker) continue;
					$k = is_int($k) ? $k : '"' . strtr($k, preg_match($re, $k) || preg_last_error() ? $tableBin : $tableUtf) . '"';
					echo "$space\t$k => ";
					self::dump($v, $level + 1);
				}
				unset($var[$marker]);
				echo "$space}";

			} else {
				echo "{\n$space\t...\n$space}";
			}
			echo "\n";

		} elseif ($var instanceof Exception) {
			echo 'Exception ', get_class($var), ': ', ($var->getCode() ? '#' . $var->getCode() . ' ' : '') . $var->getMessage(), "\n";

		} elseif (is_object($var)) {
			$arr = (array) $var;
			echo "object(" . get_class($var) . ") (" . count($arr) . ") ";
			$space = str_repeat("\t", $level);

			static $list = array();
			if (empty($arr)) {
				echo "{}";

			} elseif (in_array($var, $list, TRUE)) {
				echo "{\n$space\t*RECURSION*\n$space}";

			} elseif ($level < self::$maxDepth) {
				echo "{\n";
				$list[] = $var;
				foreach ($arr as $k => &$v) {
					$m = '';
					if ($k[0] === "\x00") {
						$m = $k[1] === '*' ? ' protected' : ' private';
						$k = substr($k, strrpos($k, "\x00") + 1);
					}
					$k = strtr($k, preg_match($re, $k) || preg_last_error() ? $tableBin : $tableUtf);
					echo "$space\t\"$k\"$m => ";
					echo self::dump($v, $level + 1);
				}
				array_pop($list);
				echo "$space}";

			} else {
				echo "{\n$space\t...\n$space}";
			}
			echo "\n";

		} elseif (is_resource($var)) {
			echo "resource of type(" . get_resource_type($var) . ")\n";

		} else {
			echo "unknown type\n";
		}
	}



	/**
	 * Custom exception handler.
	 * @param  \Exception
	 * @return void
	 */
	public static function exceptionHandler(Exception $exception)
	{
		self::dump($exception, 0);
	}



	/**
	 * Coverage saving helper.
	 * @return void
	 */
	public static function prepareSaveCoverage()
	{
		register_shutdown_function(array(__CLASS__, 'saveCoverage'));
	}



	/**
	 * Saves information about code coverage.
	 * @return void
	 */
	public static function saveCoverage()
	{
		$file = dirname(__FILE__) . '/coverage.tmp';
		$coverage = @unserialize(file_get_contents($file));
		$root = realpath(dirname(__FILE__) . '/../../Nette') . DIRECTORY_SEPARATOR;

		foreach (xdebug_get_code_coverage() as $filename => $lines) {
			if (strncmp($root, $filename, strlen($root))) continue;

			foreach ($lines as $num => $val) {
				if (empty($coverage[$filename][$num]) || $val > 0) {
					$coverage[$filename][$num] = $val; // -1 => untested; -2 => dead code
				}
			}
		}

		file_put_contents($file, serialize($coverage));
	}



	/**
	 * Skips this test.
	 * @return void
	 */
	public static function skip($message = 'No message.')
	{
		header('X-Nette-Test-Skip: '. $message);
		exit;
	}

}
