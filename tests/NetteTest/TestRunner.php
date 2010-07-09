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
 * Test runner.
 *
 * @author     David Grudl
 * @package    Nette\Test
 */
class TestRunner
{
	const OUTPUT = 'output';
	const EXPECTED = 'expect';
	const HEADERS = 'headers';

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

					$this->log($entry, $testCase->getOutput(), self::OUTPUT);
					$this->log($entry, $testCase->getExpectedOutput(), self::EXPECTED);

					if ($testCase->getExpectedHeaders() !== NULL) {
						$this->log($entry, $testCase->getHeaders(), self::OUTPUT, self::HEADERS);
						$this->log($entry, $testCase->getExpectedHeaders(), self::EXPECTED, self::HEADERS);
					}
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
	 * Returns output file for logging.
	 * @param  string
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return void
	 */
	public function log($testFile, $content, $type, $section = '')
	{
		$file = dirname($testFile) . '/' . $type . '/' . basename($testFile, '.phpt') . ($section ? ".$section" : '') . '.raw';
		@mkdir(dirname($file)); // @ - directory may already exist
		file_put_contents($file, $content);
	}



	/**
	 * Parses configuration file.
	 * @return void
	 */
	public function parseConfigFile()
	{
		$configFile = __DIR__ . '/config.ini';
		if (file_exists($configFile)) {
			$this->config = parse_ini_file($configFile, TRUE);
			if ($this->config === FALSE) {
				throw new Exception('Config file parsing failed.');
			}
			foreach ($this->config as & $environment) {
				$environment += array(
					'binary' => 'php-cgi',
					'args' => '',
					'environment' => '',
				);
				// shorthand options
				if (isset($environment['php.ini'])) {
					$environment['args'] .= ' -c '. escapeshellarg($environment['php.ini']);
				}
				if (isset($environment['libraries'])) {
					$environment['environment'] .= 'LD_LIBRARY_PATH='. escapeshellarg($environment['libraries']) .' ';
				}
			}
		}
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
				case 'e':
					$args->next();
					$name = $args->current();
					if (!isset($this->config[$name])) {
						throw new Exception("Unknown environment name '$name'.");
					}
					$this->phpBinary = $this->config[$name]['binary'];
					$this->phpArgs = $this->config[$name]['args'];
					$this->phpEnvironment = $this->config[$name]['environment'];
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
