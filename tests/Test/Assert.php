<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 *
 * @package    Nette\Test
 */



/**
 * Assertion test helpers.
 *
 * @author     David Grudl
 * @package    Nette\Test
 */
class Assert
{

	/**
	 * Checks assertion.
	 * @param  mixed  expected
	 * @param  mixed  actual
	 * @return void
	 */
	public static function same($expected, $actual)
	{
		if ($actual !== $expected) {
			self::log($expected, $actual);
			self::doFail('Failed asserting that ' . self::dump($actual) . ' is identical to expected ' . self::dump($expected));
		}
	}



	/**
	 * Checks assertion.
	 * @param  mixed  expected
	 * @param  mixed  actual
	 * @return void
	 */
	public static function equal($expected, $actual)
	{
		if ($actual != $expected) {
			self::log($expected, $actual);
			self::doFail('Failed asserting that ' . self::dump($actual) . ' is equal to expected ' . self::dump($expected));
		}
	}



	/**
	 * Checks TRUE assertion.
	 * @param  mixed  actual
	 * @return void
	 */
	public static function true($actual)
	{
		if ($actual !== TRUE) {
			self::doFail('Failed asserting that ' . self::dump($actual) . ' is TRUE');
		}
	}



	/**
	 * Checks FALSE assertion.
	 * @param  mixed  actual
	 * @return void
	 */
	public static function false($actual)
	{
		if ($actual !== FALSE) {
			self::doFail('Failed asserting that ' . self::dump($actual) . ' is FALSE');
		}
	}



	/**
	 * Checks NULL assertion.
	 * @param  mixed  actual
	 * @return void
	 */
	public static function null($actual)
	{
		if ($actual !== NULL) {
			self::doFail('Failed asserting that ' . self::dump($actual) . ' is NULL');
		}
	}



	/**
	 * Checks exception assertion.
	 * @param  string class
	 * @param  string message
	 * @param  Exception
	 * @return void
	 */
	public static function exception($class, $message, $actual)
	{
		if (!($actual instanceof $class)) {
			self::doFail('Failed asserting that ' . get_class($actual) . " is an instance of class $class");
		}
		if ($message) {
			self::match($message, $actual->getMessage());
		}
	}



	/**
	 * Checks if the function throws exception.
	 * @param  callable
	 * @param  string class
	 * @param  string message
	 * @return void
	 */
	public static function throws($function, $class, $message = NULL)
	{
		try {
			call_user_func($function);
			self::doFail('Expected exception');
		} catch (Exception $e) {
			Assert::exception($class, $message, $e);
		}
	}



	/**
	 * Checks if the function throws exception.
	 * @param  callable
	 * @param  int
	 * @param  string message
	 * @return void
	 */
	public static function error($function, $level, $message = NULL)
	{
		$catched = NULL;
		set_error_handler(function($severity, $message, $file, $line) use (& $catched) {
			if (($severity & error_reporting()) === $severity) {
				if ($catched) {
					echo "\nUnexpected error $message in $file:$line";
					exit(TestCase::CODE_FAIL);
				}
				$catched = array($severity, $message);
			}
		});
		call_user_func($function);
		restore_error_handler();

		if (!$catched) {
			self::doFail('Expected error');
		}
		if ($catched[0] !== $level) {
			$consts = get_defined_constants(TRUE);
			foreach ($consts['Core'] as $name => $val) {
				if ($catched[0] === $val && substr($name, 0, 2) === 'E_') {
					$catched[0] = $name;
				}
				if ($level === $val && substr($name, 0, 2) === 'E_') {
					$level = $name;
				}
			}
			self::doFail('Failed asserting that ' . $catched[0] . ' is ' . $level);
		}
		if ($message) {
			self::match($message, $catched[1]);
		}
	}



	/**
	 * Failed assertion
	 * @return void
	 */
	public static function fail($message)
	{
		self::doFail($message);
	}



	/**
	 * Initializes shutdown handler.
	 * @return void
	 */
	public static function handler($handler)
	{
		ob_start();
		register_shutdown_function($handler);
	}



	/**
	 * Compares results using mask:
	 *   %a%    one or more of anything except the end of line characters
	 *   %a?%   zero or more of anything except the end of line characters
	 *   %A%    one or more of anything including the end of line characters
	 *   %A?%   zero or more of anything including the end of line characters
	 *   %s%    one or more white space characters except the end of line characters
	 *   %s?%   zero or more white space characters except the end of line characters
	 *   %S%    one or more of characters except the white space
	 *   %S?%   zero or more of characters except the white space
	 *   %c%    a single character of any sort (except the end of line)
	 *   %d%    one or more digits
	 *   %d?%   zero or more digits
	 *   %i%    signed integer value
	 *   %f%    floating point number
	 *   %h%    one or more HEX digits
	 *   %ns%   PHP namespace
	 *   %[..]% reg-exp
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
			'%ds%'=> '[\\\\/]',     // directory separator
			'%[^' => '[^',          // reg-exp
			'%['  => '[',           // reg-exp
			']%'  => ']+',          // reg-exp
			'%('  => '(?:',         // reg-exp
			')%'  => ')',           // reg-exp
			')?%' => ')?',          // reg-exp

			'.' => '\.', '\\' => '\\\\', '+' => '\+', '*' => '\*', '?' => '\?', '[' => '\[', '^' => '\^', // preg quote
			']' => '\]', '$' => '\$', '(' => '\(', ')' => '\)', '{' => '\{', '}' => '\}', '=' => '\=', '!' => '\!',
			'>' => '\>', '<' => '\<', '|' => '\|', ':' => '\:', '-' => '\-', "\x00" => '\000', '#' => '\#',
		));

		$old = ini_set('pcre.backtrack_limit', '5000000');
		$res = preg_match("#^$re$#s", $actual);
		ini_set('pcre.backtrack_limit', $old);
		if ($res === FALSE || preg_last_error()) {
			throw new Exception("Error while executing regular expression. (PREG Error Code " . preg_last_error() . ")");
		}
		if (!$res) {
			self::log($expected, $actual);
			self::doFail('Failed asserting that ' . self::dump($actual) . ' matches expected ' . self::dump($expected));
		}
	}



	/**
	 * Returns message and file and line from call stack.
	 * @param  string
	 * @return void
	 */
	private static function doFail($message)
	{
		$trace = debug_backtrace();
		while (isset($trace[0]['file']) && $trace[0]['file'] === __FILE__) {
			array_shift($trace);
		}
		while ($trace) {
			if (isset($trace[0]['file'], $trace[0]['line'])) {
				$message .= "\nin " . implode(DIRECTORY_SEPARATOR, array_slice(explode(DIRECTORY_SEPARATOR, $trace[0]['file']), -3)) . ':' . $trace[0]['line'];
			}
			array_shift($trace);
		}
		echo "\n$message";
		exit(TestCase::CODE_FAIL);
	}



	/**
	 * Dumps information about a variable in readable format.
	 * @param  mixed  variable to dump
	 * @return void
	 */
	private static function dump($var)
	{
		static $tableUtf, $tableBin, $reBinary = '#[^\x09\x0A\x0D\x20-\x7E\xA0-\x{10FFFF}]#u';
		if ($tableUtf === NULL) {
			foreach (range("\x00", "\xFF") as $ch) {
				if (ord($ch) < 32 && strpos("\r\n\t", $ch) === FALSE) {
					$tableUtf[$ch] = $tableBin[$ch] = '\\x' . str_pad(dechex(ord($ch)), 2, '0', STR_PAD_LEFT);
				} elseif (ord($ch) < 127) {
					$tableUtf[$ch] = $tableBin[$ch] = $ch;
				} else {
					$tableUtf[$ch] = $ch; $tableBin[$ch] = '\\x' . dechex(ord($ch));
				}
			}
			$tableBin["\\"] = '\\\\';
			$tableBin["\r"] = '\\r';
			$tableBin["\n"] = '\\n';
			$tableBin["\t"] = '\\t';
			$tableUtf['\\x'] = $tableBin['\\x'] = '\\\\x';
		}

		if (is_bool($var)) {
			return $var ? 'TRUE' : 'FALSE';

		} elseif ($var === NULL) {
			return 'NULL';

		} elseif (is_int($var)) {
			return "$var";

		} elseif (is_float($var)) {
			$var = var_export($var, TRUE);
			return strpos($var, '.') === FALSE ? $var . '.0' : $var;

		} elseif (is_string($var)) {
			if ($cut = @iconv_strlen($var, 'UTF-8') > 100) {
				$var = iconv_substr($var, 0, 100, 'UTF-8');
			} elseif ($cut = strlen($var) > 100) {
				$var = substr($var, 0, 100);
			}
			return '"' . strtr($var, preg_match($reBinary, $var) || preg_last_error() ? $tableBin : $tableUtf) . '"' . ($cut ? ' ...' : '');

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
	 * Dumps variable in PHP format.
	 * @param  mixed  variable to dump
	 * @return void
	 */
	private static function dumpPhp(&$var, $level = 0)
	{
		if (is_float($var)) {
			$var = var_export($var, TRUE);
			return strpos($var, '.') === FALSE ? $var . '.0' : $var;

		} elseif (is_bool($var)) {
			return $var ? 'TRUE' : 'FALSE';

		} elseif (is_string($var) && (preg_match('#[^\x09\x20-\x7E\xA0-\x{10FFFF}]#u', $var) || preg_last_error())) {
			static $table;
			if ($table === NULL) {
				foreach (range("\x00", "\xFF") as $ch) {
					$table[$ch] = ord($ch) < 32 || ord($ch) >= 127
						? '\\x' . str_pad(dechex(ord($ch)), 2, '0', STR_PAD_LEFT)
						: $ch;
				}
				$table["\r"] = '\r';
				$table["\n"] = '\n';
				$table["\t"] = '\t';
				$table['$'] = '\\$';
				$table['\\'] = '\\\\';
				$table['"'] = '\\"';
			}
			return '"' . strtr($var, $table) . '"';

		} elseif (is_array($var)) {
			$s = '';
			$space = str_repeat("\t", $level);

			static $marker;
			if ($marker === NULL) {
				$marker = uniqid("\x00", TRUE);
			}
			if (empty($var)) {

			} elseif ($level > 50 || isset($var[$marker])) {
				throw new \Exception('Nesting level too deep or recursive dependency.');

			} else {
				$s .= "\n";
				$var[$marker] = TRUE;
				$counter = 0;
				foreach ($var as $k => &$v) {
					if ($k !== $marker) {
						$s .= "$space\t" . ($k === $counter ? '' : self::dumpPhp($k) . " => ") . self::dumpPhp($v, $level + 1) . ",\n";
						$counter = is_int($k) ? max($k + 1, $counter) : $counter;
					}
				}
				unset($var[$marker]);
				$s .= $space;
			}
			return "array($s)";

		} elseif (is_object($var)) {
			$arr = (array) $var;
			$s = '';
			$space = str_repeat("\t", $level);

			static $list = array();
			if (empty($arr)) {

			} elseif ($level > 50 || in_array($var, $list, TRUE)) {
				throw new \Exception('Nesting level too deep or recursive dependency.');

			} else {
				$s .= "\n";
				$list[] = $var;
				foreach ($arr as $k => &$v) {
					if ($k[0] === "\x00") {
						$k = substr($k, strrpos($k, "\x00") + 1);
					}
					$s .= "$space\t" . self::dumpPhp($k) . " => " . self::dumpPhp($v, $level + 1) . ",\n";
				}
				array_pop($list);
				$s .= $space;
			}
			return get_class($var) === 'stdClass'
				? "(object) array($s)"
				: get_class($var) . "::__set_state(array($s))";

		} else {
			return var_export($var, TRUE);
		}
	}



	/**
	 * Logs big variables to file.
	 * @param  mixed
	 * @param  mixed
	 * @return void
	 */
	private static function log($expected, $actual)
	{
		$trace = debug_backtrace();
		$item = end($trace);
		// in case of shutdown handler, we want to skip inner-code blocks
		// and debugging calls (e.g. those of Nette\Diagnostics\Debugger)
		// to get correct path to test file (which is the only purpose of this)
		while (!isset($item['file']) || substr($item['file'], -5) !== '.phpt') {
			$item = prev($trace);
			if ($item === FALSE) {
				return;
			}
		}
		$file = dirname($item['file']) . '/output/' . basename($item['file'], '.phpt');

		if (is_object($expected) || is_array($expected) || (is_string($expected) && strlen($expected) > 80)) {
			@mkdir(dirname($file)); // @ - directory may already exist
			file_put_contents($file . '.expected', is_string($expected) ? $expected : self::dumpPhp($expected));
		}

		if (is_object($actual) || is_array($actual) || (is_string($actual) && strlen($actual) > 80)) {
			@mkdir(dirname($file)); // @ - directory may already exist
			file_put_contents($file . '.actual', is_string($actual) ? $actual : self::dumpPhp($actual));
		}
	}

}
