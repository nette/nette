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

require __DIR__ . '/TestCase.php';


/**
 * Test helpers.
 *
 * @author     David Grudl
 * @package    Nette\Test
 */
class TestHelpers
{
	/** @var array */
	static public $notes = array();

	/** @var string */
	static public $coverageFile;



	/**
	 * Purges directory.
	 * @param  string
	 * @return void
	 */
	public static function purge($dir)
	{
		@mkdir($dir, 0777, TRUE); // @ - directory may already exist
		foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::CHILD_FIRST) as $entry) {
			if (substr($entry->getBasename(), 0, 1) === '.') { // . or .. or .gitignore
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
	 * Skips this test.
	 * @return void
	 */
	public static function skip($message = '')
	{
		echo "\nSkipped:\n$message\n";
		die(TestCase::CODE_SKIP);
	}



	/**
	 * locks the parallel tests.
	 * @return void
	 */
	public static function lock($name = '', $path = '')
	{
		static $lock;
		flock($lock = fopen($path . '/lock-' . md5($name), 'w'), LOCK_EX);
	}



	/**
	 * Starts gathering the information for code coverage.
	 * @param  string
	 * @return void
	 */
	public static function startCodeCoverage($file)
	{
		self::$coverageFile = $file;
		xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);
		register_shutdown_function(array(__CLASS__, 'prepareSaveCoverage'));
	}



	/**
	 * Coverage saving helper. Do not call directly.
	 * @return void
	 * @internal
	 */
	public static function prepareSaveCoverage()
	{
		register_shutdown_function(array(__CLASS__, 'saveCoverage'));
	}



	/**
	 * Saves information about code coverage. Do not call directly.
	 * @return void
	 * @internal
	 */
	public static function saveCoverage()
	{
		$f = fopen(self::$coverageFile, 'a+');
		flock($f, LOCK_EX);
		fseek($f, 0);
		$coverage = @unserialize(stream_get_contents($f));
		$root = realpath(__DIR__ . '/../../Nette') . DIRECTORY_SEPARATOR;

		foreach (xdebug_get_code_coverage() as $filename => $lines) {
			if (strncmp($root, $filename, strlen($root))) {
				continue;
			}

			foreach ($lines as $num => $val) {
				if (empty($coverage[$filename][$num]) || $val > 0) {
					$coverage[$filename][$num] = $val; // -1 => untested; -2 => dead code
				}
			}
		}

		ftruncate($f, 0);
		fwrite($f, serialize($coverage));
		fclose($f);
	}

}
