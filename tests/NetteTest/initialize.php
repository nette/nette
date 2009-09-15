<?php

/**
 * Test initialization and helpers.
 *
 * @author     David Grudl
 * @package    Nette\Test
 */

require dirname(__FILE__) . '/NetteTestCase.php';
require dirname(__FILE__) . '/../../Nette/loader.php';



Test::startup();



/**
 * Dumps information about a variable in readable format.
 * @param  mixed  variable to dump
 * @return mixed  variable itself or dump
 */
function dump($var)
{
	Test::dump($var, 0);
	echo "\n";
	return $var;
}



/**
 * Writes new section.
 * @param  string
 * @return void
 */
function section($heading = NULL)
{
	echo $heading ? "==> $heading\n\n" : "===\n\n";
}



/**
 * Writes new message.
 * @param  string
 * @return void
 */
function message($message)
{
	echo "$message\n\n";
}



/**
 * Test helpers.
 *
 * @author     David Grudl
 * @package    Nette\Test
 */
final class Test
{
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

		if (defined('TEMP_DIR')) {
			self::purge(TEMP_DIR);
		}

		if (PHP_SAPI !== 'cli') {
			header('Content-Type: text/plain; charset=utf-8');
		}
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

			} elseif ($level < 5) {
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

		} elseif (is_object($var)) {
			$arr = (array) $var;
			echo "object(" . get_class($var) . ") (" . count($arr) . ") ";
			$space = str_repeat("\t", $level);

			static $list = array();
			if (empty($arr)) {
				echo "{}";

			} elseif (in_array($var, $list, TRUE)) {
				echo "{\n$space\t*RECURSION*\n$space}";

			} elseif ($level < 5) {
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

}
