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

	/** @var string  php-cgi environment variables */
	private $phpEnvironment;

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

		exec($this->phpEnvironment . escapeshellarg($this->phpBinary) . ' -v', $output);
		if (!isset($output[0])) {
			return FALSE;

		} elseif (strpos($output[0], 'cgi-fcgi') === FALSE) {
			echo "Nette Framework Tests suite requires php-cgi, " . $this->phpBinary . " given.\n\n";
			return FALSE;
		}
		echo $this->log("$output[0] | $this->phpBinary $this->phpArgs $this->phpEnvironment\n");

		$tests = array();
		foreach ($this->paths as $path) {
			if (is_file($path)) {
				$files = array($path);
			} else {
				$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
			}
			foreach ($files as $file) {
				$file = (string) $file;
				$info = pathinfo($file);
				if (!isset($info['extension']) || $info['extension'] !== 'phpt') {
					continue;
				}
				$tests[] = $file;
			}
		}

		$running = array();
		while ($tests || $running) {
			for ($i = count($running); $tests && $i < $this->jobs; $i++) {
				$file = array_shift($tests);
				$count++;
				$testCase = new TestCase($file);
				$testCase->setPhp($this->phpBinary, $this->phpArgs, $this->phpEnvironment);
				try {
					$parallel = ($this->jobs > 1) && (count($running) + count($tests) > 1);
					$running[$file] = $testCase->run(!$parallel);
				} catch (TestCaseException $e) {
					echo 's';
					$skipped[] = $this->log($this->format('Skipped', $file, $testCase, $e));
				}
			}
			if (count($running) > 1) {
				usleep(self::RUN_USLEEP); // stream_select() doesn't work with proc_open()
			}
			foreach ($running as $file => $testCase) {
				if ($testCase->isReady()) {
					try {
						$testCase->collect();
						echo '.';
						$passed[] = array($testCase->getName(), $file);

					} catch (TestCaseException $e) {
						if ($e->getCode() === TestCaseException::SKIPPED) {
							echo 's';
							$skipped[] = $this->log($this->format('Skipped', $file, $testCase, $e));

						} else {
							echo 'F';
							$failed[] = $this->log($this->format('FAILED', $file, $testCase, $e));
						}
					}
					unset($running[$file]);
				}
			}
		}

		$failedCount = count($failed);
		$skippedCount = count($skipped);

		if ($this->displaySkipped) {
			echo "\n", implode($skipped);
		}

		if (!$count) {
			echo $this->log("No tests found\n");

		} elseif ($failedCount) {
			echo "\n", implode($failed);
			echo $this->log("\nFAILURES! ($count tests, $failedCount failures, $skippedCount skipped)");
			return FALSE;

		} else {
			echo $this->log("\n\nOK ($count tests, $skippedCount skipped)");
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
		$iniSet = FALSE;

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
					echo "Log: $file\n";
					break;
				case 'c':
					$args->next();
					$path = realpath($args->current());
					if ($path === FALSE) {
						throw new Exception("PHP configuration file '{$args->current()}' not found.");
					}
					$this->phpArgs .= " -c " . escapeshellarg($path);
					$iniSet = TRUE;
					break;
				case 'd':
					$args->next();
					$this->phpArgs .= " -d " . escapeshellarg($args->current());
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
		if (!$iniSet) {
			$this->phpArgs .= " -n";
		}
	}



	/**
	 * Writes to log
	 * @return string
	 */
	private function log($s)
	{
		if ($this->logFile) {
			fputs($this->logFile, "$s\n");
		}
		return "$s\n";
	}



	/**
	 * @return string
	 */
	private function format($s, $file, $testCase, $e)
	{
		return "\n-- $s: " . trim($testCase->getName()) . ' | '
			. implode(DIRECTORY_SEPARATOR, array_slice(explode(DIRECTORY_SEPARATOR, $file), -3))
			. str_replace("\n", "\n   ", "\n" . trim($e->getMessage())) . "\n";
	}

}
