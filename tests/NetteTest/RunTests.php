Nette Framework tests
---------------------
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

<?php
}



/**
 * Execute tests
 */
try {
	$manager = new NetteTestRunner;
	$manager->parseArguments();
	$manager->run();

} catch (Exception $e) {
	echo 'Error: ', $e->getMessage(), "\n";
	die(-1);
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

	/** @var string  PHP-CGI.exe commandline */
	private $cmdLine;

	/** @var string  */
	private $path;



	/**
	 * Runs all tests.
	 * @param  string  path
	 * @return void
	 */
	public function run()
	{
		$number = $failed = $passed = 0;

		if (is_file($this->path)) {
			$files = array($this->path);
		} else {
			$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->path));
		}

		foreach ($files as $entry) {
			$info = pathinfo($entry);
			if (!isset($info['extension']) || $info['extension'] !== 'phpt') {
				continue;
			}

			$number++;
			$testCase = new NetteTestCase($entry, $this->cmdLine);
			try {
				echo $testCase->getName(), ': ';
				$testCase->run();
				$passed++;
				echo "OK";

			} catch (NetteTestCaseException $e) {
				echo $e->getMessage();
				$failed++;

				if ($testCase->getExpectedOutput() !== NULL) {
					$this->log($entry, $testCase->getOutput(), self::OUTPUT);
					$this->log($entry, $testCase->getExpectedOutput(), self::EXPECTED);
				}
				if ($testCase->getExpectedHeaders() !== NULL) {
					$this->log($entry, $testCase->getHeaders(), self::OUTPUT, self::HEADERS);
					$this->log($entry, $testCase->getExpectedHeaders(), self::EXPECTED, self::HEADERS);
				}
			}
			echo "\n";
		}

		echo "\nTest result summary\n-------------------\n";
		echo "Number of tests: $number\n";
		echo "Tests failed: $failed\n";
		echo "Tests passed: $passed\n";
		echo "-------------------\n";
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
		$phpExecutable = 'php-cgi.exe'; // path to PHP CGI executable that is used to run the test scripts
		$phpIni = dirname(__FILE__) . '/php.ini'; // path in which to look for php.ini
		$phpArgs = '';
		$this->path = getcwd(); // current directory

		$args = new ArrayIterator(array_slice(isset($_SERVER['argv']) ? $_SERVER['argv'] : array(), 1));
		foreach ($args as $arg) {
			$opt = preg_replace('#/|-+#A', '', $arg);
			if ($opt === $arg) {
				$this->path = $arg;

			} else switch ($opt) {
				case 'p':
					$args->next();
					$phpExecutable = $args->current();
					break;
				case 'c':
					$args->next();
					$phpIni = $args->current();
					break;
				case 'd':
					$args->next();
					$phpArgs .= ' -d ' . escapeshellarg($args->current());
					break;
				default:
					echo "Unknown option -$opt\n";
					exit;
			}
		}

		$real = realpath($phpExecutable);
		if (!$real) {
			throw new Exception("Missing PHP executable file '$phpExecutable'.");
		}
		$this->cmdLine = escapeshellarg($real) . $phpArgs . ' -c ' . escapeshellarg($phpIni) . " %input > %output";
	}

}
