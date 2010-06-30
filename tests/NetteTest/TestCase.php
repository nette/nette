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



/**
 * Single test case.
 *
 * @author     David Grudl
 * @package    Nette\Test
 */
class TestCase
{
	/** @var string  test file */
	private $file;

	/** @var array   test file multiparts */
	private $sections;

	/** @var string  test output */
	private $output;

	/** @var string  output headers in raw format */
	private $headers;

	/** @var string  PHP-CGI command line */
	private $cmdLine;

	/** @var string  PHP-CGI command line */
	private $phpVersion;

	/** @var array */
	private static $cachedPhp;



	/**
	 * @param  string  test file name
	 * @param  string  PHP-CGI command line
	 * @return void
	 */
	public function __construct($testFile)
	{
		$this->file = (string) $testFile;
		$this->sections = self::parseSections($this->file);
	}



	/**
	 * Runs single test.
	 * @return void
	 */
	public function run()
	{
		// pre-skip?
		$options = $this->sections['options'];
		if (isset($options['skip'])) {
			$message = $options['skip'] ? $options['skip'] : 'No message.';
			throw new TestCaseException($message, TestCaseException::SKIPPED);

		} elseif (isset($options['phpversion'])) {
			$operator = '>=';
			if (preg_match('#^(<=|le|<|lt|==|=|eq|!=|<>|ne|>=|ge|>|gt)#', $options['phpversion'], $matches)) {
				$options['phpversion'] = trim(substr($options['phpversion'], strlen($matches[1])));
				$operator = $matches[1];
			}
			if (version_compare($options['phpversion'], $this->phpVersion, $operator)) {
				throw new TestCaseException("Requires PHP $operator $options[phpversion].", TestCaseException::SKIPPED);
			}
		}

		$this->execute();
		$output = $this->output;
		$headers = array_change_key_case(self::parseLines($this->headers, ':'), CASE_LOWER);
		$tests = 0;

		// post-skip?
		if (isset($headers['x-nette-test-skip'])) {
			throw new TestCaseException($headers['x-nette-test-skip'], TestCaseException::SKIPPED);
		}

		// compare output
		$expectedOutput = $this->getExpectedOutput();
		if ($expectedOutput !== NULL) {
			$tests++;
			$binary = (bool) preg_match('#[\x00-\x08\x0B\x0C\x0E-\x1F]#', $expectedOutput);
			if ($binary) {
				if ($expectedOutput !== $output) {
					throw new TestCaseException("Binary output doesn't match.");
				}
			} else {
				$output = self::normalize($output, isset($options['keeptrailingspaces']));
				$expectedOutput = self::normalize($expectedOutput, isset($options['keeptrailingspaces']));
				if (!$this->compare($output, $expectedOutput)) {
					throw new TestCaseException("Output doesn't match.");
				}
			}
		}

		// compare headers
		$expectedHeaders = $this->getExpectedHeaders();
		if ($expectedHeaders !== NULL) {
			$tests++;
			$expectedHeaders = self::normalize($expectedHeaders, FALSE);
			$expectedHeaders = array_change_key_case(self::parseLines($expectedHeaders, ':'), CASE_LOWER);
			foreach ($expectedHeaders as $name => $header) {
				if (!isset($headers[$name])) {
					throw new TestCaseException("Missing header '$name'.");

				} elseif (!$this->compare($headers[$name], $header)) {
					throw new TestCaseException("Header '$name' doesn't match.");
				}
			}
		}

		if (!$tests) { // expecting no output
			if (trim($output) !== '') {
				throw new TestCaseException("Empty output doesn't match.");
			}
		}
	}



	/**
	 * Sets PHP command line.
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return TestCase  provides a fluent interface
	 */
	public function setPhp($binary, $args, $environment)
	{
		if (isset(self::$cachedPhp[$binary])) {
			$this->phpVersion = self::$cachedPhp[$binary];

		} else {
			exec($environment . escapeshellarg($binary) . ' -v', $output, $res);
			if ($res !== 0 && $res !== 255) {
				throw new Exception("Unable to execute '$binary -v'.");
			}

			if (!preg_match('#^PHP (\S+).*cgi#i', $output[0], $matches)) {
				throw new Exception("Unable to detect PHP version (output: $output[0]).");
			}

			$this->phpVersion = self::$cachedPhp[$binary] = $matches[1];
		}

		$this->cmdLine = $environment . escapeshellarg($binary) . $args;
		return $this;
	}



	/**
	 * Execute test.
	 * @return array
	 */
	private function execute()
	{
		$this->headers = $this->output = NULL;

		$tempFile = tempnam('', 'tmp');
		if (!$tempFile) {
			throw new Exception("Unable to create temporary file.");
		}

		$command = $this->cmdLine;
		if (isset($this->sections['options']['phpini'])) {
			foreach (explode(';', $this->sections['options']['phpini']) as $item) {
				$command .= " -d " . escapeshellarg(trim($item));
			}
		}
		$command .= ' ' . escapeshellarg($this->file) . ' > ' . escapeshellarg($tempFile);

		chdir(dirname($this->file));
		exec($command, $foo, $res);
		if ($res === 255) {
			// exit_status 255 => parse or fatal error

		} elseif ($res !== 0) {
			throw new Exception("Unable to execute '$command'.");

		}

		$this->output = file_get_contents($tempFile);
		unlink($tempFile);

		list($this->headers, $this->output) = explode("\r\n\r\n", $this->output, 2); // CGI
	}



	/**
	 * Returns test file section.
	 * @return string
	 */
	public function getSection($name)
	{
		return isset($this->sections[$name]) ? $this->sections[$name] : NULL;
	}



	/**
	 * Returns test name.
	 * @return string
	 */
	public function getName()
	{
		return $this->sections['options']['name'];
	}



	/**
	 * Returns test output.
	 * @return string
	 */
	public function getOutput()
	{
		return $this->output;
	}



	/**
	 * Returns output headers.
	 * @return string
	 */
	public function getHeaders()
	{
		return $this->headers;
	}



	/**
	 * Returns expected output.
	 * @return string
	 */
	public function getExpectedOutput()
	{
		if (isset($this->sections['expect'])) {
			return $this->sections['expect'];

		} elseif (is_file($expFile = str_replace('.phpt', '', $this->file) . '.expect')) {
			return file_get_contents($expFile);

		} else {
			return NULL;
		}
	}



	/**
	 * Returns expected headers.
	 * @return string
	 */
	public function getExpectedHeaders()
	{
		return $this->getSection('expectheaders');
	}



	/********************* helpers ****************d*g**/



	/**
	 * Splits file into sections.
	 * @param  string  file
	 * @return array
	 */
	public static function parseSections($testFile)
	{
		$content = file_get_contents($testFile);
		$sections = array(
			'options' => array(),
		);

		// phpDoc
		$phpDoc = preg_match('#^/\*\*(.*?)\*/#ms', $content, $matches) ? trim($matches[1]) : '';
		preg_match_all('#^\s*\*\s*@(\S+)(.*)#mi', $phpDoc, $matches, PREG_SET_ORDER);
		foreach ($matches as $match) {
			$sections['options'][strtolower($match[1])] = isset($match[2]) ? trim($match[2]) : TRUE;
		}
		$sections['options']['name'] = preg_match('#^\s*\*\s*TEST:(.*)#mi', $phpDoc, $matches) ? trim($matches[1]) : $testFile;

		// file parts
		$tmp = preg_split('#^-{3,}([^\s-]+)-{1,}(?:\r?\n|$)#m', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
		$i = 1;
		while (isset($tmp[$i])) {
			$sections[strtolower($tmp[$i])] = $tmp[$i+1];
			$i += 2;
		}
		return $sections;
	}



	/**
	 * Splits HTTP headers into array.
	 * @param  string
	 * @param  string
	 * @return array
	 */
	public static function parseLines($raw, $separator)
	{
		$headers = array();
		foreach (explode("\r\n", $raw) as $header) {
			$a = strpos($header, $separator);
			if ($a !== FALSE) {
				$headers[trim(substr($header, 0, $a))] = (string) trim(substr($header, $a + 1));
			}
		}
		return $headers;
	}



	/**
	 * Compares results.
	 * @param  string
	 * @param  string
	 * @return bool
	 */
	public static function compare($left, $right)
	{
		$right = strtr($right, array(
			'%a%' => '[^\r\n]+',    // one or more of anything except the end of line characters
			'%a?%'=> '[^\r\n]*',    // zero or more of anything except the end of line characters
			'%A%' => '.+',          // one or more of anything including the end of line characters
			'%A?%'=> '.*',          // zero or more of anything including the end of line characters
			'%s%' => '[\t ]+',      // one or more white space characters except the end of line characters
			'%s?%'=> '[\t ]*',      // zero or more white space characters except the end of line characters
			'%S%' => '\S+',         // one or more of characters except the white space
			'%S?%'=> '\S*',         // zero or more of characters except the white space
			'%c%' => '[^\r\n]',     // a single character of any sort (except the end of line)
			'%d%' => '[0-9]+',      // one or more digits
			'%d?%'=> '[0-9]*',      // zero or more digits
			'%i%' => '[+-]?[0-9]+', // signed integer value
			'%f%' => '[+-]?\.?\d+\.?\d*(?:[Ee][+-]?\d+)?', // floating point number
			'%h%' => '[0-9a-fA-F]+',// one or more HEX digits
			'%ns%'=> '(?:[_0-9a-zA-Z\\\\]+\\\\|N)?',// PHP namespace
			'%[^' => '[^',          // reg-exp
			'%['  => '[',           // reg-exp
			']%'  => ']+',          // reg-exp

			'.' => '\.', '\\' => '\\\\', '+' => '\+', '*' => '\*', '?' => '\?', '[' => '\[', '^' => '\^', ']' => '\]', '$' => '\$', '(' => '\(', ')' => '\)', // preg quote
			'{' => '\{', '}' => '\}', '=' => '\=', '!' => '\!', '>' => '\>', '<' => '\<', '|' => '\|', ':' => '\:', '-' => '\-', "\x00" => '\000', '#' => '\#', // preg quote
		));

		return (bool) preg_match("#^$right$#s", $left);
	}



	/**
	 * Normalizes whitespace
	 * @param  string
	 * @param  bool
	 * @return string
	 */
	public static function normalize($s, $keepTrailingSpaces)
	{
		$s = str_replace("\n", PHP_EOL, str_replace("\r\n", "\n", $s));  // normalize EOL
		if (!$keepTrailingSpaces) {
			$s = preg_replace("#[\t ]+(\r?\n)#", '$1', $s); // multiline right trim
			$s = rtrim($s); // ending trim
		}
		return $s;
	}

}



/**
 * Single test exception.
 *
 * @author     David Grudl
 * @package    Nette\Test
 */
class TestCaseException extends Exception
{
	const SKIPPED = 1;

}
