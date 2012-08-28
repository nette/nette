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
	public $phpBinary;

	/** @var string  php-cgi command-line arguments */
	public $phpArgs;

	/** @var string  php-cgi environment variables */
	public $phpEnvironment;

	/** @var bool  display skipped tests information? */
	public $displaySkipped = FALSE;

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

		exec($this->phpEnvironment . escapeshellarg($this->phpBinary) . ' -v', $output);
		if (!isset($output[0])) {
			return FALSE;

		} elseif (strpos($output[0], 'cgi-fcgi') === FALSE) {
			echo "Nette Framework Tests suite requires php-cgi, " . $this->phpBinary . " given.\n\n";
			return FALSE;
		}
		echo "$output[0] | $this->phpBinary $this->phpArgs $this->phpEnvironment\n\n";

		$tests = array();
		foreach ($this->paths as $path) {
			if (is_file($path)) {
				$files = array($path);
			} else {
				$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
			}
			foreach ($files as $entry) {
				$entry = (string) $entry;
				$info = pathinfo($entry);
				if (!isset($info['extension']) || $info['extension'] !== 'phpt') {
					continue;
				}
				$tests[] = $entry;
			}
		}

		$running = array();
		while ($tests || $running) {
			for ($i = count($running); $tests && $i < $this->jobs; $i++) {
				$entry = array_shift($tests);
				$count++;
				$testCase = new TestCase($entry);
				$testCase->setPhp($this->phpBinary, $this->phpArgs, $this->phpEnvironment);
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
				list($name, $file, $message) = $item;
				$this->out("\n" . ($i + 1) . ") $name\n   $message\n   $file\n");
			}
		}

		if (!$count) {
			$this->out("No tests found\n");

		} elseif ($failedCount) {
			$this->out("\n\nFailures:\n");
			foreach ($failed as $item) {
				list($name, $file, $message) = $item;
				$this->out("\n-> $name\n   file: $file\n   $message\n");
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
		$this->phpEnvironment = '';
		$this->paths = array();

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
					$this->phpEnvironment .= 'LD_LIBRARY_PATH='. escapeshellarg($args->current()) . ' ';
					break;
				case 's':
					$this->displaySkipped = TRUE;
					break;
				case 'j':
					$args->next();
					$this->jobs = max(1, (int) $args->current());
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
