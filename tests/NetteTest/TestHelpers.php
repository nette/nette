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
	static public $notes = array();


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
	 * Log info.
	 * @return void
	 */
	public static function note($message)
	{
		self::$notes[] = $message;
	}



	/**
	 * Returns notes.
	 * @return array
	 */
	public static function fetchNotes()
	{
		$res = self::$notes;
		self::$notes = array();
		return $res;
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
