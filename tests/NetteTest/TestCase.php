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

	/** @var array  */
	private $options;

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
		$this->options = self::parseOptions($this->file);
	}



	/**
	 * Runs single test.
	 * @return void
	 */
	public function run()
	{
		// pre-skip?
		if (isset($this->options['skip'])) {
			$message = $this->options['skip'] ? $this->options['skip'] : 'No message.';
			throw new TestCaseException($message, TestCaseException::SKIPPED);

		} elseif (isset($this->options['phpversion'])) {
			$operator = '>=';
			if (preg_match('#^(<=|le|<|lt|==|=|eq|!=|<>|ne|>=|ge|>|gt)#', $this->options['phpversion'], $matches)) {
				$this->options['phpversion'] = trim(substr($this->options['phpversion'], strlen($matches[1])));
				$operator = $matches[1];
			}
			if (version_compare($this->options['phpversion'], $this->phpVersion, $operator)) {
				throw new TestCaseException("Requires PHP $operator {$this->options['phpversion']}.", TestCaseException::SKIPPED);
			}
		}

		$this->execute();

		// post-skip?
		if (isset($this->headers['X-Nette-Test-Skip'])) {
			throw new TestCaseException($this->headers['X-Nette-Test-Skip'], TestCaseException::SKIPPED);
		}

		// compare output
		if (trim($this->output) !== '') {
			throw new TestCaseException("Empty output doesn't match.");
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
		if (isset($this->options['phpini'])) {
			foreach (explode(';', $this->options['phpini']) as $item) {
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

		list($headers, $this->output) = explode("\r\n\r\n", $this->output, 2); // CGI

		$this->headers = array();
		foreach (explode("\r\n", $headers) as $header) {
			$a = strpos($header, ':');
			if ($a !== FALSE) {
				$this->headers[trim(substr($header, 0, $a))] = (string) trim(substr($header, $a + 1));
			}
		}
	}



	/**
	 * Returns test name.
	 * @return string
	 */
	public function getName()
	{
		return $this->options['name'];
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



	/********************* helpers ****************d*g**/



	/**
	 * Parse phpDoc.
	 * @param  string  file
	 * @return array
	 */
	public static function parseOptions($testFile)
	{
		$content = file_get_contents($testFile);
		$options = array();
		$phpDoc = preg_match('#^/\*\*(.*?)\*/#ms', $content, $matches) ? trim($matches[1]) : '';
		preg_match_all('#^\s*\*\s*@(\S+)(.*)#mi', $phpDoc, $matches, PREG_SET_ORDER);
		foreach ($matches as $match) {
			$options[strtolower($match[1])] = isset($match[2]) ? trim($match[2]) : TRUE;
		}
		$options['name'] = preg_match('#^\s*\*\s*TEST:(.*)#mi', $phpDoc, $matches) ? trim($matches[1]) : $testFile;
		return $options;
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
