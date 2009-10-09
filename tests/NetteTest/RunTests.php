
Nette Test Framework (v0.1)
---------------------------
<?php

require_once dirname(__FILE__) . '/NetteTestCase.php';



/**
 * Help
 */
if (!isset($_SERVER['argv'][1])) { ?>
Usage:
	php RunTests.php [options] [file or directory]

Options:
	-p <php>    Specify PHP-CGI executable to run.
	-c <path>   Look for php.ini in directory <path> or use <path> as php.ini.
	-d key=val  Define INI entry 'key' with value 'val'.
	-l <path>   Specify path to shared library files (LD_LIBRARY_PATH)

<?php
}



/**
 * Execute tests
 */
try {
	@unlink(dirname(__FILE__) . '/coverage.tmp'); // intentionally @

	$manager = new NetteTestRunner;
	$manager->parseArguments();
	$res = $manager->run();
	die($res ? 1 : 0);

} catch (Exception $e) {
	echo 'Error: ', $e->getMessage(), "\n";
	die(2);
}



/**
 * Test runner.
 *
 * @author     David Grudl
 * @package    Nette\Test
 */
class NetteTestRunner
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
			$testCase = new NetteTestCase($entry);
			$testCase->setPhp($this->phpBinary, $this->phpArgs, $this->phpEnvironment);

			try {
				$testCase->run();
				echo '.';
				$passed[] = array($testCase->getName(), $entry);

			} catch (NetteTestCaseException $e) {
				if ($e->getCode() === NetteTestCaseException::SKIPPED) {
					echo 's';
					$skipped[] = array($testCase->getName(), $entry);

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

		/*
		if ($skippedCount) {
			echo "\n\nSkipped:\n";
			foreach ($skipped as $i => $item) {
				list($name, $file) = $item;
				echo "\n", ($i + 1), ") $name\n   $file\n";
			}
		}*/

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
		@mkdir(dirname($file)); // intentionally @
		file_put_contents($file, $content);
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
			$opt = preg_replace('#/|-+#A', '', $arg);
			if ($opt === $arg) {
				$this->path = $arg;

			} else switch ($opt) {
				case 'p':
					$args->next();
					$this->phpBinary = $args->current();
					break;
				case 'c':
				case 'd':
					$args->next();
					$this->phpArgs .= " -$opt " . escapeshellarg($args->current());
					break;
				case 'l':
					$args->next();
					$this->phpEnvironment .= 'LD_LIBRARY_PATH='. escapeshellarg($args->current()) . ' ';
					break;
				default:
					echo "Error: Unknown option -$opt\n";
					exit;
			}
		}
	}

}
