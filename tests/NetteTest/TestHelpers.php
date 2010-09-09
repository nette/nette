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
	 * Skips this test.
	 * @return void
	 */
	public static function skip($message = '')
	{
		echo "\nSkipped $message";
		die(TestCase::CODE_SKIP);
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

}
