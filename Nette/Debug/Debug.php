<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 */

namespace Nette;

use Nette,
	Nette\Environment;



/**
 * Debugger: displays and logs errors.
 *
 * Behavior is determined by two factors: mode & output
 * - modes: production / development
 * - output: HTML / AJAX / CLI / other (e.g. XML)
 *
 * @author     David Grudl
 */
final class Debug
{
	/** @var bool in production mode is suppressed any debugging output */
	public static $productionMode;

	/** @var bool in console mode is omitted HTML output */
	public static $consoleMode;

	/** @var int timestamp with microseconds of the start of the request */
	public static $time;

	/** @var bool is Firebug & FireLogger detected? */
	private static $firebugDetected;

	/** @var bool is AJAX request detected? */
	private static $ajaxDetected;

	/** @var string  requested URI or command line */
	public static $source;

	/********************* Debug::dump() ****************d*g**/

	/** @var int  how many nested levels of array/object properties display {@link Debug::dump()} */
	public static $maxDepth = 3;

	/** @var int  how long strings display {@link Debug::dump()} */
	public static $maxLen = 150;

	/** @var int  display location? {@link Debug::dump()} */
	public static $showLocation = FALSE;

	/********************* errors and exceptions reporing ****************d*g**/

	/**#@+ server modes {@link Debug::enable()} */
	const DEVELOPMENT = FALSE;
	const PRODUCTION = TRUE;
	const DETECT = NULL;
	/**#@-*/

	/** @var bool determines whether any error will cause immediate death */
	public static $strictMode = FALSE; // $immediateDeath

	/** @var bool disables the @ (shut-up) operator so that notices and warnings are no longer hidden */
	public static $scream = FALSE;

	/** @var array of callbacks specifies the functions that are automatically called after fatal error */
	public static $onFatalError = array();

	/** @var string name of the directory where errors should be logged; FALSE means that logging is disabled */
	public static $logDirectory;

	/** @var string e-mail to sent error notifications */
	public static $email;

	/** @var callback handler for sending emails */
	public static $mailer = array(__CLASS__, 'defaultMailer');

	/** @var int interval for sending email is 2 days */
	public static $emailSnooze = 172800;

	/** @var string URL pattern mask to open editor */
	public static $editor = 'editor://open/?file=%file&line=%line';

	/** @var bool {@link Debug::enable()} */
	private static $enabled = FALSE;

	/** @var mixed {@link Debug::tryError()} FALSE means catching is disabled */
	private static $lastError = FALSE;

	/********************* debug bar ****************d*g**/

	/** @var bool determines whether show Debug Bar */
	public static $showBar = TRUE;

	/** @var array */
	private static $panels = array();

	/** @var array payload filled by {@link Debug::barDump()} */
	private static $dumps;

	/** @var array payload filled by {@link Debug::_errorHandler()} */
	private static $errors;

	/********************* Firebug extension ****************d*g**/

	/**#@+ {@link Debug::log()} and {@link Debug::fireLog()} */
	const DEBUG = 'debug';
	const INFO = 'info';
	const WARNING = 'warning';
	const ERROR = 'error';
	const CRITICAL = 'critical';
	/**#@-*/



	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new \LogicException("Cannot instantiate static class " . get_class($this));
	}



	/**
	 * Static class constructor.
	 * @internal
	 */
	public static function _init()
	{
		self::$time = microtime(TRUE);
		self::$consoleMode = PHP_SAPI === 'cli';
		self::$productionMode = self::DETECT;
		if (self::$consoleMode) {
			self::$source = empty($_SERVER['argv']) ? 'cli' : 'cli: ' . $_SERVER['argv'][0];
		} else {
			self::$firebugDetected = isset($_SERVER['HTTP_X_FIRELOGGER']);
			self::$ajaxDetected = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
			if (isset($_SERVER['REQUEST_URI'])) {
				self::$source = (isset($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'], 'off') ? 'https://' : 'http://')
					. (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : ''))
					. $_SERVER['REQUEST_URI'];
			}
		}

		$tab = array(__CLASS__, 'renderTab'); $panel = array(__CLASS__, 'renderPanel');
		self::addPanel(new DebugPanel('time', $tab, $panel));
		self::addPanel(new DebugPanel('memory', $tab, $panel));
		self::addPanel(new DebugPanel('errors', $tab, $panel));
		self::addPanel(new DebugPanel('dumps', $tab, $panel));
	}



	/********************* useful tools ****************d*g**/



	/**
	 * Dumps information about a variable in readable format.
	 * @param  mixed  variable to dump
	 * @param  bool   return output instead of printing it? (bypasses $productionMode)
	 * @return mixed  variable itself or dump
	 */
	public static function dump($var, $return = FALSE)
	{
		if (!$return && self::$productionMode) {
			return $var;
		}

		$output = "<pre class=\"nette-dump\">" . self::_dump($var, 0) . "</pre>\n";

		if (!$return && self::$showLocation) {
			$trace = debug_backtrace();
			$i = isset($trace[1]['class']) && $trace[1]['class'] === __CLASS__ ? 1 : 0;
			if (isset($trace[$i]['file'], $trace[$i]['line'])) {
				$output = substr_replace($output, ' <small>' . htmlspecialchars("in file {$trace[$i]['file']} on line {$trace[$i]['line']}", ENT_NOQUOTES) . '</small>', -8, 0);
			}
		}

		if (self::$consoleMode) {
			$output = htmlspecialchars_decode(strip_tags($output), ENT_NOQUOTES);
		}

		if ($return) {
			return $output;

		} else {
			echo $output;
			return $var;
		}
	}



	/**
	 * Dumps information about a variable in Nette Debug Bar.
	 * @param  mixed  variable to dump
	 * @param  string optional title
	 * @return mixed  variable itself
	 */
	public static function barDump($var, $title = NULL)
	{
		if (!self::$productionMode) {
			$dump = array();
			foreach ((is_array($var) ? $var : array('' => $var)) as $key => $val) {
				$dump[$key] = self::_dump($val, 0);
			}
			self::$dumps[] = array('title' => $title, 'dump' => $dump);
		}
		return $var;
	}



	/**
	 * Internal dump() implementation.
	 * @param  mixed  variable to dump
	 * @param  int    current recursion level
	 * @return string
	 */
	private static function _dump(&$var, $level)
	{
		static $tableUtf, $tableBin, $reBinary = '#[^\x09\x0A\x0D\x20-\x7E\xA0-\x{10FFFF}]#u';
		if ($tableUtf === NULL) {
			foreach (range("\x00", "\xFF") as $ch) {
				if (ord($ch) < 32 && strpos("\r\n\t", $ch) === FALSE) $tableUtf[$ch] = $tableBin[$ch] = '\\x' . str_pad(dechex(ord($ch)), 2, '0', STR_PAD_LEFT);
				elseif (ord($ch) < 127) $tableUtf[$ch] = $tableBin[$ch] = $ch;
				else { $tableUtf[$ch] = $ch; $tableBin[$ch] = '\\x' . dechex(ord($ch)); }
			}
			$tableBin["\\"] = '\\\\';
			$tableBin["\r"] = '\\r';
			$tableBin["\n"] = '\\n';
			$tableBin["\t"] = '\\t';
			$tableUtf['\\x'] = $tableBin['\\x'] = '\\\\x';
		}

		if (is_bool($var)) {
			return ($var ? 'TRUE' : 'FALSE') . "\n";

		} elseif ($var === NULL) {
			return "NULL\n";

		} elseif (is_int($var)) {
			return "$var\n";

		} elseif (is_float($var)) {
			$var = (string) $var;
			if (strpos($var, '.') === FALSE) $var .= '.0';
			return "$var\n";

		} elseif (is_string($var)) {
			if (self::$maxLen && strlen($var) > self::$maxLen) {
				$s = htmlSpecialChars(substr($var, 0, self::$maxLen), ENT_NOQUOTES) . ' ... ';
			} else {
				$s = htmlSpecialChars($var, ENT_NOQUOTES);
			}
			$s = strtr($s, preg_match($reBinary, $s) || preg_last_error() ? $tableBin : $tableUtf);
			$len = strlen($var);
			return "\"$s\"" . ($len > 1 ? " ($len)" : "") . "\n";

		} elseif (is_array($var)) {
			$s = "<span>array</span>(" . count($var) . ") ";
			$space = str_repeat($space1 = '   ', $level);
			$brackets = range(0, count($var) - 1) === array_keys($var) ? "[]" : "{}";

			static $marker;
			if ($marker === NULL) $marker = uniqid("\x00", TRUE);
			if (empty($var)) {

			} elseif (isset($var[$marker])) {
				$brackets = $var[$marker];
				$s .= "$brackets[0] *RECURSION* $brackets[1]";

			} elseif ($level < self::$maxDepth || !self::$maxDepth) {
				$s .= "<code>$brackets[0]\n";
				$var[$marker] = $brackets;
				foreach ($var as $k => &$v) {
					if ($k === $marker) continue;
					$k = is_int($k) ? $k : '"' . strtr($k, preg_match($reBinary, $k) || preg_last_error() ? $tableBin : $tableUtf) . '"';
					$s .= "$space$space1$k => " . self::_dump($v, $level + 1);
				}
				unset($var[$marker]);
				$s .= "$space$brackets[1]</code>";

			} else {
				$s .= "$brackets[0] ... $brackets[1]";
			}
			return $s . "\n";

		} elseif (is_object($var)) {
			$arr = (array) $var;
			$s = "<span>" . get_class($var) . "</span>(" . count($arr) . ") ";
			$space = str_repeat($space1 = '   ', $level);

			static $list = array();
			if (empty($arr)) {

			} elseif (in_array($var, $list, TRUE)) {
				$s .= "{ *RECURSION* }";

			} elseif ($level < self::$maxDepth || !self::$maxDepth) {
				$s .= "<code>{\n";
				$list[] = $var;
				foreach ($arr as $k => &$v) {
					$m = '';
					if ($k[0] === "\x00") {
						$m = $k[1] === '*' ? ' <span>protected</span>' : ' <span>private</span>';
						$k = substr($k, strrpos($k, "\x00") + 1);
					}
					$k = strtr($k, preg_match($reBinary, $k) || preg_last_error() ? $tableBin : $tableUtf);
					$s .= "$space$space1\"$k\"$m => " . self::_dump($v, $level + 1);
				}
				array_pop($list);
				$s .= "$space}</code>";

			} else {
				$s .= "{ ... }";
			}
			return $s . "\n";

		} elseif (is_resource($var)) {
			return "<span>" . get_resource_type($var) . " resource</span>\n";

		} else {
			return "<span>unknown type</span>\n";
		}
	}



	/**
	 * Starts/stops stopwatch.
	 * @param  string  name
	 * @return float   elapsed seconds
	 */
	public static function timer($name = NULL)
	{
		static $time = array();
		$now = microtime(TRUE);
		$delta = isset($time[$name]) ? $now - $time[$name] : 0;
		$time[$name] = $now;
		return $delta;
	}



	/********************* errors and exceptions reporing ****************d*g**/



	/**
	 * Enables displaying or logging errors and exceptions.
	 * @param  mixed         production, development mode, autodetection or IP address(es) whitelist.
	 * @param  string        error log directory; enables logging in production mode, FALSE means that logging is disabled
	 * @param  string        administrator email; enables email sending in production mode
	 * @return void
	 */
	public static function enable($mode = NULL, $logDirectory = NULL, $email = NULL)
	{
		error_reporting(E_ALL | E_STRICT);

		// production/development mode detection
		if (is_bool($mode)) {
			self::$productionMode = $mode;

		} elseif (is_string($mode)) { // IP addresses
			$mode = preg_split('#[,\s]+#', "$mode 127.0.0.1 ::1");
		}

		if (is_array($mode)) { // IP addresses whitelist detection
			self::$productionMode = !isset($_SERVER['REMOTE_ADDR']) || !in_array($_SERVER['REMOTE_ADDR'], $mode, TRUE);
		}

		if (self::$productionMode === self::DETECT) {
			if (class_exists('Nette\Environment')) {
				self::$productionMode = Environment::isProduction();

			} elseif (isset($_SERVER['SERVER_ADDR']) || isset($_SERVER['LOCAL_ADDR'])) { // IP address based detection
				$addrs = array();
				if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) { // proxy server detected
					$addrs = preg_split('#,\s*#', $_SERVER['HTTP_X_FORWARDED_FOR']);
				}
				if (isset($_SERVER['REMOTE_ADDR'])) {
					$addrs[] = $_SERVER['REMOTE_ADDR'];
				}
				$addrs[] = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : $_SERVER['LOCAL_ADDR'];
				self::$productionMode = FALSE;
				foreach ($addrs as $addr) {
					$oct = explode('.', $addr);
					if ($addr !== '::1' && (count($oct) !== 4 || ($oct[0] !== '10' && $oct[0] !== '127' && ($oct[0] !== '172' || $oct[1] < 16 || $oct[1] > 31)
						&& ($oct[0] !== '169' || $oct[1] !== '254') && ($oct[0] !== '192' || $oct[1] !== '168')))
					) {
						self::$productionMode = TRUE;
						break;
					}
				}

			} else {
				self::$productionMode = !self::$consoleMode;
			}
		}

		// logging configuration
		if (is_string($logDirectory) || $logDirectory === FALSE) {
			self::$logDirectory = $logDirectory;
		} else {
			self::$logDirectory = defined('APP_DIR') ? APP_DIR . '/../log' : getcwd() . '/log';
		}
		if (self::$logDirectory) {
			ini_set('error_log', self::$logDirectory . '/php_error.log');
		}

		// php configuration
		if (function_exists('ini_set')) {
			ini_set('display_errors', !self::$productionMode); // or 'stderr'
			ini_set('html_errors', FALSE);
			ini_set('log_errors', FALSE);

		} elseif (ini_get('display_errors') != !self::$productionMode && ini_get('display_errors') !== (self::$productionMode ? 'stderr' : 'stdout')) { // intentionally ==
			throw new \NotSupportedException('Function ini_set() must be enabled.');
		}

		if ($email) {
			if (!is_string($email)) {
				throw new \InvalidArgumentException('E-mail address must be a string.');
			}
			self::$email = $email;
		}

		if (!defined('E_DEPRECATED')) {
			define('E_DEPRECATED', 8192);
		}

		if (!defined('E_USER_DEPRECATED')) {
			define('E_USER_DEPRECATED', 16384);
		}

		if (!self::$enabled) {
			register_shutdown_function(array(__CLASS__, '_shutdownHandler'));
			set_exception_handler(array(__CLASS__, '_exceptionHandler'));
			set_error_handler(array(__CLASS__, '_errorHandler'));
			self::$enabled = TRUE;
		}
	}



	/**
	 * Is Debug enabled?
	 * @return bool
	 */
	public static function isEnabled()
	{
		return self::$enabled;
	}



	/**
	 * Logs message or exception to file (if not disabled) and sends e-mail notification (if enabled).
	 * @param  string|Exception
	 * @param  int
	 * @return void
	 */
	public static function log($message, $priority = self::INFO)
	{
		if (self::$logDirectory === FALSE) {
			return;

		} elseif (!self::$logDirectory) {
			throw new \InvalidStateException('Logging directory is not specified in Nette\Debug::$logDirectory.');

		} elseif (!is_dir(self::$logDirectory)) {
			throw new \DirectoryNotFoundException("Directory '" . self::$logDirectory . "' is not found or is not directory.");
		}

		if ($message instanceof \Exception) {
			$exception = $message;
			$message = "PHP Fatal error: "
				. ($message instanceof \FatalErrorException ? $exception->getMessage() : "Uncaught exception " . get_class($exception) . " with message '" . $exception->getMessage() . "'")
				. " in " . $exception->getFile() . ":" . $exception->getLine();
		}

		error_log(@date('[Y-m-d H-i-s] ') . trim($message) . (self::$source ? '  @  ' . self::$source : '') . PHP_EOL, 3, self::$logDirectory . '/' . strtolower($priority) . '.log');

		if (($priority === self::ERROR || $priority === self::CRITICAL) && self::$email
			&& @filemtime(self::$logDirectory . '/email-sent') + self::$emailSnooze < time() // @ - file may not exist
			&& @file_put_contents(self::$logDirectory . '/email-sent', 'sent')) { // @ - file may not be writable
			call_user_func(self::$mailer, $message);
		}

		if (isset($exception)) {
			$hash = md5($exception /*5.2*. (method_exists($exception, 'getPrevious') ? $exception->getPrevious() : (isset($exception->previous) ? $exception->previous : ''))*/);
			foreach (new \DirectoryIterator(self::$logDirectory) as $entry) {
				if (strpos($entry, $hash)) {
					$skip = TRUE; break;
				}
			}
			if (empty($skip) && $logHandle = @fopen(self::$logDirectory . "/exception " . @date('Y-m-d H-i-s') . " $hash.html", 'w')) {
				ob_start(); // double buffer prevents sending HTTP headers in some PHP
				ob_start(function($buffer) use ($logHandle) { fwrite($logHandle, $buffer); }, 1);
				self::paintBlueScreen($exception);
				ob_end_flush();
				ob_end_clean();
				fclose($logHandle);
			}
		}
	}



	/**
	 * Shutdown handler to catch fatal errors and execute of the planned activities.
	 * @return void
	 * @internal
	 */
	public static function _shutdownHandler()
	{
		// 1) fatal error handler
		static $types = array(
			E_ERROR => 1,
			E_CORE_ERROR => 1,
			E_COMPILE_ERROR => 1,
			E_PARSE => 1,
		);
		$error = error_get_last();
		if (isset($types[$error['type']])) {
			$template = String::match($error['file'], '~(?P<module>[A-z0-9_-]*)_(?P<presenter>[A-z0-9_-]+).(?P<action>[A-z0-9_-]+).phtml~im');
			if ($template !== NULL) {
				self::paintTemplateError(APP_DIR . ($template['module'] !== '' ? '/' . $template['module'] : '') . '/templates/' . $template['presenter'] . '/' . $template['action'] . '.phtml', $error['message'], $error['file'], $error['line'] - 1);
			}
			self::_exceptionHandler(new \FatalErrorException($error['message'], 0, $error['type'], $error['file'], $error['line'], NULL));
			return;
		}

		// 2) debug bar (require HTML & development mode)
		if (self::$showBar && !self::$productionMode && !self::$ajaxDetected && !self::$consoleMode
			&& (!preg_match('#^Content-Type: (?!text/html)#im', implode("\n", headers_list())))) {
			self::paintDebugBar();
		}
	}



	/**
	 * Handler to catch uncaught exception.
	 * @param  \Exception
	 * @return void
	 * @internal
	 */
	public static function _exceptionHandler(\Exception $exception)
	{
		if (!headers_sent()) { // for PHP < 5.2.4
			header('HTTP/1.1 500 Internal Server Error');
		}

		$htmlMode = !self::$ajaxDetected && !preg_match('#^Content-Type: (?!text/html)#im', implode("\n", headers_list()));

		try {
			if (self::$productionMode) {
				self::log($exception, self::ERROR);

				if (self::$consoleMode) {
					echo "ERROR: the server encountered an internal error and was unable to complete your request.\n";

				} elseif ($htmlMode) {
					echo "<!DOCTYPE html><meta name=robots content=noindex><meta name=generator content='Nette Framework'>\n\n";
					echo "<style>body{color:#333;background:white;width:500px;margin:100px auto}h1{font:bold 47px/1.5 sans-serif;margin:.6em 0}p{font:21px/1.5 Georgia,serif;margin:1.5em 0}small{font-size:70%;color:gray}</style>\n\n";
					echo "<title>Server Error</title>\n\n<h1>Server Error</h1>\n\n<p>We're sorry! The server encountered an internal error and was unable to complete your request. Please try again later.</p>\n\n<p><small>error 500</small></p>";
				}

			} else {
				if (self::$consoleMode) { // dump to console
					echo "$exception\n";

				} elseif ($htmlMode) { // dump to browser
					if ($exception instanceof \Nette\Templates\MacroException) {
						self::paintTemplateError($exception->getFile(), $exception->getMessage());
					}
					self::paintBlueScreen($exception);

				} elseif (!self::fireLog($exception, self::ERROR)) { // AJAX or non-HTML mode
					self::log($exception);
				}
			}

			foreach (self::$onFatalError as $handler) {
				call_user_func($handler, $exception);
			}
		} catch (\Exception $e) {
			echo "\nNette\\Debug FATAL ERROR: thrown ", get_class($e), ': ', $e->getMessage(), "\nwhile processing ", get_class($exception), ': ', $exception->getMessage(), "\n";
			exit;
		}
	}



	/**
	 * Handler to catch warnings and notices.
	 * @param  int    level of the error raised
	 * @param  string error message
	 * @param  string file that the error was raised in
	 * @param  int    line number the error was raised at
	 * @param  array  an array of variables that existed in the scope the error was triggered in
	 * @return bool   FALSE to call normal error handler, NULL otherwise
	 * @throws \FatalErrorException
	 * @internal
	 */
	public static function _errorHandler($severity, $message, $file, $line, $context)
	{
		if (self::$lastError !== FALSE) { // tryError mode
			self::$lastError = new \ErrorException($message, 0, $severity, $file, $line);
			return NULL;
		}

		if (self::$scream) {
			error_reporting(E_ALL | E_STRICT);
		}

		if ($severity === E_RECOVERABLE_ERROR || $severity === E_USER_ERROR) {
			throw new \FatalErrorException($message, 0, $severity, $file, $line, $context);

		} elseif (($severity & error_reporting()) !== $severity) {
			return FALSE; // calls normal error handler to fill-in error_get_last()

		} elseif (self::$strictMode && !self::$productionMode) {
			$template = String::match($file, '~(?P<module>[A-z0-9_-]*)_(?P<presenter>[A-z0-9_-]+).(?P<action>[A-z0-9_-]+).phtml~im'); // missing modules
			if ($template !== NULL) {
				self::paintTemplateError(APP_DIR . ($template['module'] !== '' ? '/' . $template['module'] : '') . '/templates/' . $template['presenter'] . '/' . $template['action'] . '.phtml', $message);
			}
			self::_exceptionHandler(new \FatalErrorException($message, 0, $severity, $file, $line, $context));
			exit;
		}

		static $types = array(
			E_WARNING => 'Warning',
			E_COMPILE_WARNING => 'Warning', // currently unable to handle
			E_USER_WARNING => 'Warning',
			E_NOTICE => 'Notice',
			E_USER_NOTICE => 'Notice',
			E_STRICT => 'Strict standards',
			E_DEPRECATED => 'Deprecated',
			E_USER_DEPRECATED => 'Deprecated',
		);

		$message = 'PHP ' . (isset($types[$severity]) ? $types[$severity] : 'Unknown error') . ": $message";
		$count = & self::$errors["$message|$file|$line"];

		if ($count++) { // repeated error
			return NULL;

		} elseif (self::$productionMode) {
			self::log("$message in $file:$line", self::ERROR); // log manually, required on some stupid hostings
			return NULL;

		} else {
			$ok = self::fireLog(new \ErrorException($message, 0, $severity, $file, $line), self::WARNING);
			return self::$consoleMode || (!self::$showBar && !$ok) ? FALSE : NULL;
		}

		return FALSE; // call normal error handler
	}



	/**
	 * Processes template errors and paints bluescreen. Exits the application.
	 * @author Mikulas Dite
	 * @param string $file
	 * @param string $message
	 * @param string $originalFile
	 * @param int $originalLine
	 */
	public static function paintTemplateError($file, $message, $originalFile = NULL, $originalLine = NULL)
	{
		$content = file_get_contents($file);
		$shortpath = String::replace($file, "~" . APP_DIR . "~i");

		if ($originalFile !== NULL) {
			$originalContent = file_get_contents($originalFile);
			$lines = explode("\n", $originalContent);
			$errorContent = $lines[$originalLine];
		}
		

		// undefined variable
		if (($match = String::match($message, '~^Undefined variable: (?P<var>[A-z0-9_]+)$~im')) !== NULL) {
			$block = String::match($content, '~.*?' . preg_quote('$' . $match['var']) . '~s');
			$line = substr_count($block[0], "\n") + 1;
			self::paintBlueScreen(new \Nette\Templates\MacroException("Undefined variable `$" . "{$match['var']}` on line $line in `$shortpath`.", 0, NULL, $file, $line));
			exit;

		// invalid eval call {=foo()}
		} elseif (($match = String::match($message, '~^Call to undefined function (?P<function>[A-z_]+)~im')) !== NULL) {
			$block = String::match($content, '~.*?\{(\?|!)?=?' . $match['function'] . '\(~is');
			$line = substr_count($block[0], "\n") + 1;
			self::paintBlueScreen(new \Nette\Templates\MacroException("Called undefined function `{$match['function']}` on line $line in `$shortpath`.", 0, NULL, $file, $line));
			exit;

		// unopened macro
		} elseif (($match = String::match($message, '~^syntax error, unexpected T_END(?P<keyword>[A-Z_]+)~im')) !== NULL) {
			$keyword = String::lower($match['keyword']);
			$block = String::match($content, '~.*?\{/' . $keyword . '~is');
			$line = substr_count($block[0], "\n") + 1;
			self::paintBlueScreen(new \Nette\Templates\MacroException("Unopened macro `$keyword` on line $line in `$shortpath`.", 0, NULL, $file, $line));
			exit;

		// unknown macro
		} elseif (($match = String::match($message, '~Unknown macro (?P<macro>\{.*\}) on line~im')) !== NULL) {
			$block = String::match($content, '~.*?' . preg_quote($match['macro']) . '~s');
			$line = substr_count($block[0], "\n") + 1;
			self::paintBlueScreen(new \Nette\Templates\MacroException("Unknown macro `{$match['macro']}` on line $line in `$shortpath`.", 0, NULL, $file, $line));
			exit;

		// unclosed command macro (for, foreach, while, if)
		} elseif (String::match($message, '~^syntax error, unexpected \'}\'$~im')) {
			$block = String::match($originalContent, '~.*: \?>~s');
			$line = substr_count($block[0], "\n");
			$errorContent = $lines[$line];

		// unclosed macro
		} elseif (String::match($message, '~^syntax error, unexpected \$end$~im')) {
			$block = String::match($originalContent, '~.*?\{ \?>~s');
			$line = substr_count($block[0], "\n");
			$errorContent = $lines[$line];
		}

		// unclosed cache
		if (($match = String::match($errorContent, '~if \(Nette\\\\Templates\\\\Caching~ims')) !== NULL) {
			$block = String::match($content, '~.*\{cache~s');
			$line = substr_count($block[0], "\n") + 1;
			self::paintBlueScreen(new \Nette\Templates\MacroException("Unclosed macro `cache` on line $line in `$shortpath`.", 0, NULL, $file, $line));

		// invalid foreach
		} elseif (($match = String::match($errorContent, '~^<\?php foreach.*?Iterator\((?P<value>[^)]*)\)~ims')) !== NULL) {
			$block = String::match($content, '~.*?\{foreach ?' . preg_quote($match['value']) . '~s');
			$line = substr_count($block[0], "\n") + 1;
			self::paintBlueScreen(new \Nette\Templates\MacroException("Invalid macro `foreach` on line $line in `$shortpath`.", 0, NULL, $file, $line));

		// invalid macro
		} elseif (($match = String::match($errorContent, '~^<\?php (?P<keyword>[A-z]+).*?\((?P<value>[^)]*)\)~ims')) !== NULL) {
			$block = String::match($content, '~.*?\{' . $match['keyword'] . ' ?' . preg_quote($match['value']) . '~s');
			$line = substr_count($block[0], "\n") + 1;
			self::paintBlueScreen(new \Nette\Templates\MacroException("Invalid macro `{$match['keyword']}` on line $line in `$shortpath`.", 0, NULL, $file, $line));

		} else {
			self::paintBlueScreen(new \Nette\Templates\MacroException("Unknown template macro error in `$shortpath`.", 0, NULL, $file, 0));
		}

		exit;
	}



	/** @deprecated */
	public static function processException(\Exception $exception)
	{
		trigger_error(__METHOD__ . '() is deprecated; use ' . __CLASS__ . '::log($exception, Debug::ERROR) instead.', E_USER_WARNING);
		self::log($exception, self::ERROR);
	}



	/**
	 * Handles exception throwed in __toString().
	 * @param  \Exception
	 * @return void
	 */
	public static function toStringException(\Exception $exception)
	{
		if (self::$enabled) {
			self::_exceptionHandler($exception);
		} else {
			trigger_error($exception->getMessage(), E_USER_ERROR);
		}
		exit;
	}



	/**
	 * Paint blue screen.
	 * @param  \Exception
	 * @return void
	 * @internal
	 */
	public static function paintBlueScreen(\Exception $exception)
	{
		if (class_exists('Nette\Environment', FALSE)) {
			$application = Environment::getContext()->hasService('Nette\\Application\\Application', TRUE) ? Environment::getContext()->getService('Nette\\Application\\Application') : NULL;
		}

		require __DIR__ . '/templates/bluescreen.phtml';
	}



	/**
	 * Paint debug bar.
	 * @return void
	 * @internal
	 */
	public static function paintDebugBar()
	{
		$panels = array();
		foreach (self::$panels as $panel) {
			$panels[] = array(
				'id' => preg_replace('#[^a-z0-9]+#i', '-', $panel->getId()),
				'tab' => $tab = (string) $panel->getTab(),
				'panel' => $tab ? (string) $panel->getPanel() : NULL,
			);
		}
		require __DIR__ . '/templates/bar.phtml';
	}



	/**
	 * Starts catching potential errors/warnings.
	 * @return void
	 */
	public static function tryError()
	{
		if (!self::$enabled && self::$lastError === FALSE) {
			set_error_handler(array(__CLASS__, '_errorHandler'));
		}
		self::$lastError = NULL;
	}



	/**
	 * Returns catched error/warning message.
	 * @param  \ErrorException  catched error
	 * @return bool
	 */
	public static function catchError(& $error)
	{
		if (!self::$enabled && self::$lastError !== FALSE) {
			restore_error_handler();
		}
		$error = self::$lastError;
		self::$lastError = FALSE;
		return (bool) $error;
	}



	/**
	 * Default mailer.
	 * @param  string
	 * @return void
	 */
	private static function defaultMailer($message)
	{
		$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] :
				(isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '');

		$parts = str_replace(
			array("\r\n", "\n"),
			array("\n", PHP_EOL),
			array(
				'headers' => "From: noreply@$host\nX-Mailer: Nette Framework\n",
				'subject' => "PHP: An error occurred on the server $host",
				'body' => "[" . @date('Y-m-d H:i:s') . "] $message", // @ - timezone may not be set
			)
		);

		mail(self::$email, $parts['subject'], $parts['body'], $parts['headers']);
	}



	/********************* debug bar ****************d*g**/



	/**
	 * Add custom panel.
	 * @param  IDebugPanel
	 * @return void
	 */
	public static function addPanel(IDebugPanel $panel)
	{
		self::$panels[] = $panel;
	}



	/**
	 * Renders default panel.
	 * @param  string
	 * @return void
	 * @internal
	 */
	public static function renderTab($id)
	{
		switch ($id) {
		case 'time':
			require __DIR__ . '/templates/bar.time.tab.phtml';
			return;
		case 'memory':
			require __DIR__ . '/templates/bar.memory.tab.phtml';
			return;
		case 'dumps':
			if (!Debug::$dumps) return;
			require __DIR__ . '/templates/bar.dumps.tab.phtml';
			return;
		case 'errors':
			if (!Debug::$errors) return;
			require __DIR__ . '/templates/bar.errors.tab.phtml';
		}
	}



	/**
	 * Renders default panel.
	 * @param  string
	 * @return void
	 * @internal
	 */
	public static function renderPanel($id)
	{
		switch ($id) {
		case 'dumps':
			require __DIR__ . '/templates/bar.dumps.panel.phtml';
			return;
		case 'errors':
			require __DIR__ . '/templates/bar.errors.panel.phtml';
		}
	}



	/********************* Firebug extension ****************d*g**/



	/**
	 * Sends message to FireLogger console.
	 * @see http://firelogger.binaryage.com
	 * @param  mixed   message to log
	 * @return bool    was successful?
	 */
	public static function fireLog($message)
	{
		if (self::$productionMode) {
			return;

		} elseif (!self::$firebugDetected || headers_sent()) {
			return FALSE;
		}

		static $payload = array('logs' => array());

		$item = array(
			'name' => 'PHP',
			'level' => 'debug',
			'order' => count($payload['logs']),
			'time' => str_pad(number_format((microtime(TRUE) - self::$time) * 1000, 1, '.', ' '), 8, '0', STR_PAD_LEFT) . ' ms',
			'template' => '',
			'message' => '',
			'style' => 'background:#767ab6',
		);

		$args = func_get_args();
		if (isset($args[0]) && is_string($args[0])) {
			$item['template'] = array_shift($args);
		}

		if (isset($args[0]) && $args[0] instanceof \Exception) {
			$e = array_shift($args);
			$trace = $e->getTrace();
			if (isset($trace[0]['class']) && $trace[0]['class'] === __CLASS__ && ($trace[0]['function'] === '_shutdownHandler' || $trace[0]['function'] === '_errorHandler')) {
				unset($trace[0]);
			}

			$item['exc_info'] = array(
				$e->getMessage(),
				$e->getFile(),
				array(),
			);
			$item['exc_frames'] = array();

			foreach ($trace as $frame) {
				$frame += array('file' => null, 'line' => null, 'class' => null, 'type' => null, 'function' => null, 'object' => null, 'args' => null);
				$item['exc_info'][2][] = array($frame['file'], $frame['line'], "$frame[class]$frame[type]$frame[function]", $frame['object']);
				$item['exc_frames'][] = $frame['args'];
			};

			$file = str_replace(dirname(dirname(dirname($e->getFile()))), "\xE2\x80\xA6", $e->getFile());
			$item['template'] = ($e instanceof \ErrorException ? '' : get_class($e) . ': ') . $e->getMessage() . ($e->getCode() ? ' #' . $e->getCode() : '') . ' in ' . $file . ':' . $e->getLine();
			array_unshift($trace, array('file' => $e->getFile(), 'line' => $e->getLine()));

		} else {
			$trace = debug_backtrace();
			if (isset($trace[0]['class']) && $trace[0]['class'] === __CLASS__ && ($trace[0]['function'] === '_shutdownHandler' || $trace[0]['function'] === '_errorHandler')) {
				unset($trace[0]);
			}
		}

		if (isset($args[0]) && in_array($args[0], array(self::DEBUG, self::INFO, self::WARNING, self::ERROR, self::CRITICAL), TRUE)) {
			$item['level'] = array_shift($args);
		}

		$item['args'] = $args;

		foreach ($trace as $frame) {
			if (isset($frame['file']) && is_file($frame['file'])) {
				$item['pathname'] = $frame['file'];
				$item['lineno'] = $frame['line'];
				break;
			}
		}

		$payload['logs'][] = $item;
		foreach (str_split(base64_encode(@json_encode($payload)), 4990) as $k => $v) {
			header("FireLogger-de11e-$k:$v");
		}
		return TRUE;
	}



	/**
	 * Internal dump() implementation.
	 * @param  mixed  variable to dump
	 * @param  int    current recursion level
	 * @return string
	 */
	private static function fireDump(&$var, $level = 0)
	{
		if (is_bool($var) || is_null($var) || is_int($var) || is_float($var)) {
			return $var;

		} elseif (is_string($var)) {
			if (self::$maxLen && strlen($var) > self::$maxLen) {
				$var = substr($var, 0, self::$maxLen) . " \xE2\x80\xA6 ";
			}
			return @iconv('UTF-16', 'UTF-8//IGNORE', iconv('UTF-8', 'UTF-16//IGNORE', $var)); // intentionally @

		} elseif (is_array($var)) {
			static $marker;
			if ($marker === NULL) $marker = uniqid("\x00", TRUE);
			if (isset($var[$marker])) {
				return "\xE2\x80\xA6RECURSION\xE2\x80\xA6";

			} elseif ($level < self::$maxDepth || !self::$maxDepth) {
				$var[$marker] = TRUE;
				$res = array();
				foreach ($var as $k => &$v) {
					if ($k !== $marker) $res[self::fireDump($k)] = self::fireDump($v, $level + 1);
			}
				unset($var[$marker]);
				return $res;

			} else {
				return " \xE2\x80\xA6 ";
		}

		} elseif (is_object($var)) {
			$arr = (array) $var;
			static $list = array();
			if (in_array($var, $list, TRUE)) {
				return "\xE2\x80\xA6RECURSION\xE2\x80\xA6";

			} elseif ($level < self::$maxDepth || !self::$maxDepth) {
				$list[] = $var;
				$res = array(" \xC2\xBBclass\xC2\xAB" => get_class($var));
				foreach ($arr as $k => &$v) {
					if ($k[0] === "\x00") {
						$k = substr($k, strrpos($k, "\x00") + 1);
					}
					$res[self::fireDump($k)] = self::fireDump($v, $level + 1);
				}
				array_pop($list);
				return $res;

			} else {
				return " \xE2\x80\xA6 ";
			}

		} elseif (is_resource($var)) {
			return "resource " . get_resource_type($var);

		} else {
			return "unknown type";
		}
	}

}



Debug::_init();
