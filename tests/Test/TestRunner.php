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

require __DIR__ . '/TestHelpers.php';



/**
 * Test runner.
 *
 * @author     David Grudl
 * @package    Nette\Test
 */
class TestRunner
{
	/** waiting time between runs in microseconds */
	const RUN_USLEEP = 10000;

	/** @var array  paths to test files/directories */
	public $paths = array();

	/** @var resource */
	private $logFile;

	/** @var string  php-cgi binary */
	private $phpBinary;

	/** @var string  php-cgi command-line arguments */
	private $phpArgs;

	/** @var array  php-cgi environment variables */
	private $phpEnvironment = array();

	/** @var array  list of database drivers for database tests */
	private $databaseDrivers;

	/** @var bool  display skipped tests information? */
	private $displaySkipped = FALSE;

	/** @var int  run jobs in parallel */
	private $jobs = 1;



	/**
	 * Runs all tests.
	 * @return void
	 */
	public function run()
	{
		$count = 0;
		$failed = $passed = $skipped = array();

		exec(escapeshellarg($this->phpBinary) . $this->phpArgs . ' -v', $output);
		echo "$output[0] | {$this->phpBinary}{$this->phpArgs}" . PHP_EOL . PHP_EOL;

		$tests = array();
		foreach ($this->paths as $path) {
			if (is_file($path)) {
				$files = array($path);
			} else {
				$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
			}

			foreach ($files as $testFile) {
				$testFile = (string) $testFile;
				$info = pathinfo($testFile);
				if (!isset($info['extension']) || $info['extension'] !== 'phpt') {
					continue;
				}

				$options = TestCase::parseOptions($testFile);
				if (isset($options['databases'])) {
					$databaseDrivers = preg_split('/, */', $options['databases']);
					if ($this->databaseDrivers !== NULL) {
						$databaseDrivers = array_intersect($this->databaseDrivers, $databaseDrivers);
					}

					foreach ($databaseDrivers as $databaseDriver) {
						$tests[] = array($testFile, "$testFile ($databaseDriver)", array('NETTE_DATABASE_DRIVER' => $databaseDriver));
					}

				} else {
					$tests[] = array($testFile, $testFile, array());
				}
			}
		}

		$running = array();
		while ($tests || $running) {
			for ($i = count($running); $tests && $i < $this->jobs; $i++) {
				list($testFile, $entry, $localEnvironment) = array_shift($tests);
				$count++;
				$testCase = new TestCase($testFile);
				$testCase->setPhp($this->phpBinary, $this->phpArgs, $this->phpEnvironment + $localEnvironment);
				try {
					$parallel = ($this->jobs > 1) && (count($running) + count($tests) > 1);
					$running[$entry] = $testCase->run(!$parallel);
				} catch (TestCaseException $e) {
					$this->out('s');
					$skipped[] = array($testCase->getName(), $entry, $e->getMessage());
				}
			}
			if (count($running) > 1) {
				usleep(self::RUN_USLEEP); // stream_select() doesn't work with proc_open()
			}
			foreach ($running as $entry => $testCase) {
				if ($testCase->isReady()) {
					try {
						$testCase->collect();
						$this->out('.');
						$passed[] = array($testCase->getName(), $entry);

					} catch (TestCaseException $e) {
						if ($e->getCode() === TestCaseException::SKIPPED) {
							$this->out('s');
							$skipped[] = array($testCase->getName(), $entry, $e->getMessage());

						} else {
							$this->out('F');
							$failed[] = array($testCase->getName(), $entry, $e->getMessage());
						}
					}
					unset($running[$entry]);
				}
			}
		}

		$failedCount = count($failed);
		$skippedCount = count($skipped);

		if ($this->displaySkipped && $skippedCount) {
			$this->out("\n\nSkipped:\n");
			foreach ($skipped as $i => $item) {
				list($name, $entry, $message) = $item;
				$this->out("\n" . ($i + 1) . ") $name\n   $message\n   $entry\n");
			}
		}

		if (!$count) {
			$this->out("No tests found\n");

		} elseif ($failedCount) {
			$this->out("\n\nFailures:\n");
			foreach ($failed as $item) {
				list($name, $entry, $message) = $item;
				$this->out("\n-> $name\n   file: $entry\n   $message\n");
			}
			$this->out("\nFAILURES! ($count tests, $failedCount failures, $skippedCount skipped)\n");
			return FALSE;

		} else {
			$this->out("\n\nOK ($count tests, $skippedCount skipped)\n");
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
		$this->phpEnvironment = count($_ENV) ? $_ENV : $_SERVER;
		$this->paths = array();

		unset($this->phpEnvironment['argv'], $this->phpEnvironment['argc']); // proc_open() screams "array to string conversion"

		$args = new ArrayIterator(array_slice(isset($_SERVER['argv']) ? $_SERVER['argv'] : array(), 1));
		foreach ($args as $arg) {
			if (!preg_match('#^[-/][a-z]+$#', $arg)) {
				if ($path = realpath($arg)) {
					$this->paths[] = $path;
				} else {
					throw new Exception("Invalid path '$arg'.");
				}

			} else switch (substr($arg, 1)) {
				case 'p':
					$args->next();
					$this->phpBinary = $args->current();
					break;

				case 'log':
					$args->next();
					$this->logFile = fopen($file = $args->current(), 'w');
					$this->out("Log: $file\n");
					break;

				case 'c':
				case 'd':
					$args->next();
					$this->phpArgs .= " -$arg[1] " . escapeshellarg($args->current());
					break;

				case 'l':
					$args->next();
					$this->phpEnvironment['LD_LIBRARY_PATH'] = $args->current();
					break;

				case 's':
					$this->displaySkipped = TRUE;
					break;

				case 'j':
					$args->next();
					$this->jobs = max(1, (int) $args->current());
					break;

				case 'db':
					$args->next();
					$this->databaseDrivers = explode(',', $args->current());
					break;

				default:
					throw new Exception("Unknown option $arg.");
					exit;
			}
		}

		if (!$this->paths) {
			$this->paths[] = getcwd(); // current directory
		}
	}



	/**
	 * Writes to display and log
	 * @return void
	 */
	private function out($s)
	{
		echo $s;
		if ($this->logFile) {
			fputs($this->logFile, $s);
		}
	}

}
