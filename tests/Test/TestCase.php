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



/**
 * Single test case.
 *
 * @author     David Grudl
 * @package    Nette\Test
 */
class TestCase
{
	const
		CODE_NONE = -1,
		CODE_OK = 0,
		CODE_SKIP = 253,
		CODE_ERROR = 255,
		CODE_FAIL = 254;

	/** @var string  test file */
	private $file;

	/** @var string  test arguments */
	private $args;

	/** @var array  */
	private $options;

	/** @var string  test output */
	private $output;

	/** @var string  output headers in raw format */
	private $headers;

	/** @var string  PHP-CGI command line */
	private $cmdLine;

	/** @var string  PHP version */
	private $phpVersion;

	/** @var string PHP type (CGI or CLI) */
	private $phpType;

	/** @var resource */
	private $proc;

	/** @var resource */
	private $stdout;

	/** @var int */
	private $exitCode = self::CODE_NONE;

	/** @var array */
	private static $cachedPhp;



	/**
	 * @param  string  test file name
	 * @param  string  PHP-CGI command line
	 * @return void
	 */
	public function __construct($testFile, $args = NULL)
	{
		$this->file = (string) $testFile;
		$this->args = $args;
		$this->options = self::parseOptions($this->file);
	}



	/**
	 * Runs single test.
	 * @param  bool
	 * @return TestCase  provides a fluent interface
	 */
	public function run($blocking = true)
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

		$this->execute($blocking);
		return $this;
	}



	/**
	 * Sets PHP command line.
	 * @param  string
	 * @param  string
	 * @return TestCase  provides a fluent interface
	 */
	public function setPhp($binary, $args)
	{
		if (isset(self::$cachedPhp[$binary])) {
			list($this->phpVersion, $this->phpType) = self::$cachedPhp[$binary];

		} else {
			exec(escapeshellarg($binary) . ' -v', $output, $res);
			if ($res !== self::CODE_OK && $res !== self::CODE_ERROR) {
				throw new Exception("Unable to execute '$binary -v'.");
			}

			if (!preg_match('#^PHP (\S+).*c(g|l)i#i', $output[0], $matches)) {
				throw new Exception("Unable to detect PHP version (output: $output[0]).");
			}

			$this->phpVersion = $matches[1];
			$this->phpType = strcasecmp($matches[2], 'g') ? 'CLI' : 'CGI';
			self::$cachedPhp[$binary] = array($this->phpVersion, $this->phpType);
		}

		$this->cmdLine = escapeshellarg($binary) . $args;
		return $this;
	}



	/**
	 * Execute test.
	 * @param  bool
	 * @return void
	 */
	private function execute($blocking)
	{
		$this->headers = $this->output = NULL;

		if (isset($this->options['phpini'])) {
			foreach (explode(';', $this->options['phpini']) as $item) {
				$this->cmdLine .= " -d " . escapeshellarg(trim($item));
			}
		}
		$this->cmdLine .= ' ' . escapeshellarg($this->file) . ' ' . $this->args;

		$descriptors = array(
			array('pipe', 'r'),
			array('pipe', 'w'),
			array('pipe', 'w'),
		);

		$this->proc = proc_open($this->cmdLine, $descriptors, $pipes, dirname($this->file), null, array('bypass_shell' => true));
		list($stdin, $this->stdout, $stderr) = $pipes;
		fclose($stdin);
		stream_set_blocking($this->stdout, $blocking ? 1 : 0);
		fclose($stderr);
	}



	/**
	 * Checks if the test results are ready.
	 * @return bool
	 */
	public function isReady()
	{
		$this->output .= stream_get_contents($this->stdout);
		$status = proc_get_status($this->proc);
		if ($status['exitcode'] !== self::CODE_NONE) {
			$this->exitCode = $status['exitcode'];
		}
		return !$status['running'];
	}



	/**
	 * Collect results.
	 * @return void
	 */
	public function collect()
	{
		$this->output .= stream_get_contents($this->stdout);
		fclose($this->stdout);
		$res = proc_close($this->proc);
		if ($res === self::CODE_NONE) {
			$res = $this->exitCode;
		}

		if ($this->phpType === 'CGI') {
			list($headers, $this->output) = explode("\r\n\r\n", $this->output, 2);
		} else {
			$headers = '';
		}

		$this->headers = array();
		foreach (explode("\r\n", $headers) as $header) {
			$a = strpos($header, ':');
			if ($a !== FALSE) {
				$this->headers[trim(substr($header, 0, $a))] = (string) trim(substr($header, $a + 1));
			}
		}

		if ($res === self::CODE_ERROR) {
			throw new TestCaseException($this->output ?: 'Fatal error');

		} elseif ($res === self::CODE_FAIL) {
			throw new TestCaseException($this->output);

		} elseif ($res === self::CODE_SKIP) { // skip
			throw new TestCaseException($this->output, TestCaseException::SKIPPED);

		} elseif ($res !== self::CODE_OK) {
			throw new Exception("Unable to execute '$this->cmdLine'.");

		}

		// HTTP code check
		if (isset($this->options['assertcode'])) {
			$code = isset($this->headers['Status']) ? (int) $this->headers['Status'] : 200;
			if ($code !== (int) $this->options['assertcode']) {
				throw new TestCaseException('Expected HTTP code ' . $this->options['assertcode'] . ' is not same as actual code ' . $code);
			}
		}
	}



	/**
	 * Returns test file path.
	 * @return string
	 */
	public function getFile()
	{
		return $this->file;
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
	 * Returns test args.
	 * @return string
	 */
	public function getArguments()
	{
		return $this->args;
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
