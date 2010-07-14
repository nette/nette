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

require __DIR__ . '/TestCase.php';


/**
 * Test helpers.
 *
 * @author     David Grudl
 * @package    Nette\Test
 */
class TestHelpers
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
		@mkdir($dir); // @ - directory may already exist
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
			self::$sections[$file] = TestCase::parseSections($file);
		}

		$lowerSection = strtolower($section);
		if (!isset(self::$sections[$file][$lowerSection])) {
			throw new Exception("Missing section '$section' in file '$file'.");
		}

		if (in_array($section, array('GET', 'POST', 'SERVER'), TRUE)) {
			return TestCase::parseLines(self::$sections[$file][$lowerSection], '=');
		} else {
			return self::$sections[$file][$lowerSection];
		}
	}



	/**
	 * Writes new message.
	 * @param  string
	 * @return void
	 */
	public static function note($message = NULL)
	{
		echo $message ? "$message\n\n" : "===\n\n";
	}



	/**
	 * Dumps information about a variable in readable format.
	 * @param  mixed  variable to dump
	 * @param  string
	 * @return mixed  variable itself or dump
	 */
	public static function dump($var, $message = NULL)
	{
		if ($message) {
			echo $message . (preg_match('#[.:?]$#', $message) ? ' ' : ': ');
		}

		self::_dump($var, 0);
		echo "\n";
		return $var;
	}



	private static function _dump(& $var, $level = 0)
	{
		static $tableUtf, $tableBin, $reBinary = '#[^\x09\x0A\x0D\x20-\x7E\xA0-\x{10FFFF}]#u';
		if ($tableUtf === NULL) {
			foreach (range("\x00", "\xFF") as $ch) {
				if (ord($ch) < 32 && strpos("\r\n\t", $ch) === FALSE) $tableUtf[$ch] = $tableBin[$ch] = '\\x' . str_pad(dechex(ord($ch)), 2, '0', STR_PAD_LEFT);
				elseif (ord($ch) < 127) $tableUtf[$ch] = $tableBin[$ch] = $ch;
				else { $tableUtf[$ch] = $ch; $tableBin[$ch] = '\\x' . dechex(ord($ch)); }
			}
			$tableBin["\\"] = '\\\\';
			$tableBin["\r"] = '\\r';
			$tableBin["\n"] = '\\n';
			$tableBin["\t"] = '\\t';
			$tableUtf['\\x'] = $tableBin['\\x'] = '\\\\x';
		}

		if (is_bool($var)) {
			echo ($var ? 'TRUE' : 'FALSE') . "\n";

		} elseif ($var === NULL) {
			echo "NULL\n";

		} elseif (is_int($var)) {
			echo "$var\n";

		} elseif (is_float($var)) {
			$var = (string) $var;
			if (strpos($var, '.') === FALSE) $var .= '.0';
			echo "$var\n";

		} elseif (is_string($var)) {
			$s = strtr($var, preg_match($reBinary, $var) || preg_last_error() ? $tableBin : $tableUtf);
			echo "\"$s\"\n";

		} elseif (is_array($var)) {
			echo "array(";
			$space = str_repeat("\t", $level);

			static $marker;
			if ($marker === NULL) $marker = uniqid("\x00", TRUE);
			if (empty($var)) {

			} elseif (isset($var[$marker])) {
				echo " *RECURSION* ";

			} elseif ($level < self::$maxDepth) {
				echo "\n";
				$vector = range(0, count($var) - 1) === array_keys($var);
				$var[$marker] = 0;
				foreach ($var as $k => &$v) {
					if ($k === $marker) continue;
					if ($vector) {
						echo "$space\t";
					} else {
						$k = is_int($k) ? $k : '"' . strtr($k, preg_match($reBinary, $k) || preg_last_error() ? $tableBin : $tableUtf) . '"';
						echo "$space\t$k => ";
					}
					self::_dump($v, $level + 1);
				}
				unset($var[$marker]);
				echo "$space";

			} else {
				echo " ... ";
			}
			echo ")\n";

		} elseif ($var instanceof Exception) {
			echo 'Exception ', get_class($var), ': ', ($var->getCode() ? '#' . $var->getCode() . ' ' : '') . $var->getMessage(), "\n";

		} elseif (is_object($var)) {
			$arr = (array) $var;
			echo get_class($var) . "(";
			$space = str_repeat("\t", $level);

			static $list = array();
			if (empty($arr)) {

			} elseif (in_array($var, $list, TRUE)) {
				echo " *RECURSION* ";

			} elseif ($level < self::$maxDepth) {
				echo "\n";
				$list[] = $var;
				foreach ($arr as $k => &$v) {
					$m = '';
					if ($k[0] === "\x00") {
						$m = $k[1] === '*' ? ' protected' : ' private';
						$k = substr($k, strrpos($k, "\x00") + 1);
					}
					$k = strtr($k, preg_match($reBinary, $k) || preg_last_error() ? $tableBin : $tableUtf);
					echo "$space\t\"$k\"$m => ";
					echo self::_dump($v, $level + 1);
				}
				array_pop($list);
				echo "$space";

			} else {
				echo " ... ";
			}
			echo ")\n";

		} elseif (is_resource($var)) {
			echo get_resource_type($var) . " resource\n";

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
		echo 'Error: Uncaught ';
		echo $exception;
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
		$file = __DIR__ . '/coverage.tmp';
		$coverage = @unserialize(file_get_contents($file));
		$root = realpath(__DIR__ . '/../../Nette') . DIRECTORY_SEPARATOR;

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
