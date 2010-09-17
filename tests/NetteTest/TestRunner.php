<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 * @package    Nette\Test
 */

require __DIR__ . '/TestCase.php';



/**
 * Test runner.
 *
 * @author     David Grudl
 * @package    Nette\Test
 */
class TestRunner
{
	/** @var string  path to test file/directory */
	public $path;

	/** @var string  php-cgi binary */
	public $phpBinary;

	/** @var string  php-cgi command-line arguments */
	public $phpArgs;

	/** @var string  php-cgi environment variables */
	public $phpEnvironment;

	/** @var bool  display skipped tests information? */
	public $displaySkipped = FALSE;



	/**
	 * Runs all tests.
	 * @return void
	 */
	public function run()
	{
		$count = 0;
		$failed = $passed = $skipped = array();

		if (is_file($this->path)) {
			$files = array($this->path);
		} else {
			$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->path));
		}

		foreach ($files as $entry) {
			$entry = (string) $entry;
			$info = pathinfo($entry);
			if (!isset($info['extension']) || $info['extension'] !== 'phpt') {
				continue;
			}

			$count++;
			$testCase = new TestCase($entry);
			$testCase->setPhp($this->phpBinary, $this->phpArgs, $this->phpEnvironment);

			try {
				$testCase->run();
				echo '.';
				$passed[] = array($testCase->getName(), $entry);

			} catch (TestCaseException $e) {
				if ($e->getCode() === TestCaseException::SKIPPED) {
					echo 's';
					$skipped[] = array($testCase->getName(), $entry, $e->getMessage());

				} else {
					echo 'F';
					$failed[] = array($testCase->getName(), $entry, $e->getMessage());
				}
			}
		}

		$failedCount = count($failed);
		$skippedCount = count($skipped);

		if ($this->displaySkipped && $skippedCount) {
			echo "\n\nSkipped:\n";
			foreach ($skipped as $i => $item) {
				list($name, $file, $message) = $item;
				echo "\n", ($i + 1), ") $name\n   $message\n   $file\n";
			}
		}

		if (!$count) {
			echo "No tests found\n";

		} elseif ($failedCount) {
			echo "\n\nFailures:\n";
			foreach ($failed as $i => $item) {
				list($name, $file, $message) = $item;
				echo "\n", ($i + 1), ") $name\n   $message\n   $file\n";
			}
			echo "\nFAILURES! ($count tests, $failedCount failures, $skippedCount skipped)\n";
			return FALSE;

		} else {
			echo "\n\nOK ($count tests, $skippedCount skipped)\n";
		}
		return TRUE;
	}



	/**
	 * Parses command line arguments.
	 * @return void
	 */
	public function parseArguments()
	{
		$this->phpBinary = 'php-cgi';
		$this->phpArgs = '';
		$this->phpEnvironment = '';
		$this->path = getcwd(); // current directory

		$args = new ArrayIterator(array_slice(isset($_SERVER['argv']) ? $_SERVER['argv'] : array(), 1));
		foreach ($args as $arg) {
			if (!preg_match('#^[-/][a-z]$#', $arg)) {
				if ($path = realpath($arg)) {
					$this->path = $path;
				} else {
					throw new Exception("Invalid path '$arg'.");
				}

			} else switch ($arg[1]) {
				case 'p':
					$args->next();
					$this->phpBinary = $args->current();
					break;
				case 'c':
				case 'd':
					$args->next();
					$this->phpArgs .= " -$arg[1] " . escapeshellarg($args->current());
					break;
				case 'l':
					$args->next();
					$this->phpEnvironment .= 'LD_LIBRARY_PATH='. escapeshellarg($args->current()) . ' ';
					break;
				case 's':
					$this->displaySkipped = TRUE;
					break;
				default:
					throw new Exception("Unknown option -$arg[1].");
					exit;
			}
		}
	}

}
