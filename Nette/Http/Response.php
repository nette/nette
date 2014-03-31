<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Http;

use Nette,
	Nette\Utils\DateTime;


/**
 * HttpResponse class.
 *
 * @author     David Grudl
 *
 * @property   int $code
 * @property-read bool $sent
 * @property-read array $headers
 */
class Response extends Nette\Object implements IResponse
{
	/** @var bool  Send invisible garbage for IE 6? */
	private static $fixIE = TRUE;

	/** @var string The domain in which the cookie will be available */
	public $cookieDomain = '';

	/** @var string The path in which the cookie will be available */
	public $cookiePath = '/';

	/** @var string Whether the cookie is available only through HTTPS */
	public $cookieSecure = FALSE;

	/** @var string Whether the cookie is hidden from client-side */
	public $cookieHttpOnly = TRUE;

	/** @var int HTTP response code */
	private $code = self::S200_OK;


	public function __construct()
	{
		if (PHP_VERSION_ID >= 50400) {
			if (is_int(http_response_code())) {
				$this->code = http_response_code();
			}
			header_register_callback($this->removeDuplicateCookies);
		}
	}


	/**
	 * Sets HTTP response code.
	 * @param  int
	 * @return self
	 * @throws Nette\InvalidArgumentException  if code is invalid
	 * @throws Nette\InvalidStateException  if HTTP headers have been sent
	 */
	public function setCode($code)
	{
		$code = (int) $code;
		if ($code < 100 || $code > 599) {
			throw new Nette\InvalidArgumentException("Bad HTTP response '$code'.");
		}
		self::checkHeaders();
		$this->code = $code;
		$protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
		header($protocol . ' ' . $code, TRUE, $code);
		return $this;
	}


	/**
	 * Returns HTTP response code.
	 * @return int
	 */
	public function getCode()
	{
		return $this->code;
	}


	/**
	 * Sends a HTTP header and replaces a previous one.
	 * @param  string  header name
	 * @param  string  header value
	 * @return self
	 * @throws Nette\InvalidStateException  if HTTP headers have been sent
	 */
	public function setHeader($name, $value)
	{
		self::checkHeaders();
		if ($value === NULL) {
			header_remove($name);
		} elseif (strcasecmp($name, 'Content-Length') === 0 && ini_get('zlib.output_compression')) {
			// ignore, PHP bug #44164
		} else {
			header($name . ': ' . $value, TRUE, $this->code);
		}
		return $this;
	}


	/**
	 * Adds HTTP header.
	 * @param  string  header name
	 * @param  string  header value
	 * @return self
	 * @throws Nette\InvalidStateException  if HTTP headers have been sent
	 */
	public function addHeader($name, $value)
	{
		self::checkHeaders();
		header($name . ': ' . $value, FALSE, $this->code);
		return $this;
	}


	/**
	 * Sends a Content-type HTTP header.
	 * @param  string  mime-type
	 * @param  string  charset
	 * @return self
	 * @throws Nette\InvalidStateException  if HTTP headers have been sent
	 */
	public function setContentType($type, $charset = NULL)
	{
		$this->setHeader('Content-Type', $type . ($charset ? '; charset=' . $charset : ''));
		return $this;
	}


	/**
	 * Redirects to a new URL. Note: call exit() after it.
	 * @param  string  URL
	 * @param  int     HTTP code
	 * @return void
	 * @throws Nette\InvalidStateException  if HTTP headers have been sent
	 */
	public function redirect($url, $code = self::S302_FOUND)
	{
		$this->setCode($code);
		$this->setHeader('Location', $url);
		echo "<h1>Redirect</h1>\n\n<p><a href=\"" . htmlSpecialChars($url, ENT_IGNORE | ENT_QUOTES) . "\">Please click here to continue</a>.</p>";
	}


	/**
	 * Sets the number of seconds before a page cached on a browser expires.
	 * @param  string|int|DateTime  time, value 0 means "until the browser is closed"
	 * @return self
	 * @throws Nette\InvalidStateException  if HTTP headers have been sent
	 */
	public function setExpiration($time)
	{
		if (!$time) { // no cache
			$this->setHeader('Cache-Control', 's-maxage=0, max-age=0, must-revalidate');
			$this->setHeader('Expires', 'Mon, 23 Jan 1978 10:00:00 GMT');
			return $this;
		}

		$time = DateTime::from($time);
		$this->setHeader('Cache-Control', 'max-age=' . ($time->format('U') - time()));
		$this->setHeader('Expires', self::date($time));
		return $this;
	}


	/**
	 * Checks if headers have been sent.
	 * @return bool
	 */
	public function isSent()
	{
		return headers_sent();
	}


	/**
	 * Return the value of the HTTP header.
	 * @param  string
	 * @param  mixed
	 * @return mixed
	 */
	public function getHeader($header, $default = NULL)
	{
		$header .= ':';
		$len = strlen($header);
		foreach (headers_list() as $item) {
			if (strncasecmp($item, $header, $len) === 0) {
				return ltrim(substr($item, $len));
			}
		}
		return $default;
	}


	/**
	 * Returns a list of headers to sent.
	 * @return array
	 */
	public function getHeaders()
	{
		$headers = array();
		foreach (headers_list() as $header) {
			$a = strpos($header, ':');
			$headers[substr($header, 0, $a)] = (string) substr($header, $a + 2);
		}
		return $headers;
	}


	/**
	 * Returns HTTP valid date format.
	 * @param  string|int|DateTime
	 * @return string
	 */
	public static function date($time = NULL)
	{
		$time = DateTime::from($time);
		$time->setTimezone(new \DateTimeZone('GMT'));
		return $time->format('D, d M Y H:i:s \G\M\T');
	}


	/**
	 * @return void
	 */
	public function __destruct()
	{
		if (self::$fixIE && isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE ') !== FALSE
			&& in_array($this->code, array(400, 403, 404, 405, 406, 408, 409, 410, 500, 501, 505), TRUE)
			&& preg_match('#^text/html(?:;|$)#', $this->getHeader('Content-Type', 'text/html'))
		) {
			echo Nette\Utils\Random::generate(2e3, " \t\r\n"); // sends invisible garbage for IE
			self::$fixIE = FALSE;
		}
	}


	/**
	 * Sends a cookie.
	 * @param  string name of the cookie
	 * @param  string value
	 * @param  string|int|DateTime  expiration time, value 0 means "until the browser is closed"
	 * @param  string
	 * @param  string
	 * @param  bool
	 * @param  bool
	 * @return self
	 * @throws Nette\InvalidStateException  if HTTP headers have been sent
	 */
	public function setCookie($name, $value, $time, $path = NULL, $domain = NULL, $secure = NULL, $httpOnly = NULL)
	{
		self::checkHeaders();
		setcookie(
			$name,
			$value,
			$time ? DateTime::from($time)->format('U') : 0,
			$path === NULL ? $this->cookiePath : (string) $path,
			$domain === NULL ? $this->cookieDomain : (string) $domain,
			$secure === NULL ? $this->cookieSecure : (bool) $secure,
			$httpOnly === NULL ? $this->cookieHttpOnly : (bool) $httpOnly
		);
		$this->removeDuplicateCookies();
		return $this;
	}


	/**
	 * Deletes a cookie.
	 * @param  string name of the cookie.
	 * @param  string
	 * @param  string
	 * @param  bool
	 * @return void
	 * @throws Nette\InvalidStateException  if HTTP headers have been sent
	 */
	public function deleteCookie($name, $path = NULL, $domain = NULL, $secure = NULL)
	{
		$this->setCookie($name, FALSE, 0, $path, $domain, $secure);
	}


	/**
	 * Removes duplicate cookies from response.
	 * @return void
	 */
	public function removeDuplicateCookies()
	{
		if (headers_sent($file, $line) || ini_get('suhosin.cookie.encrypt')) {
			return;
		}

		$flatten = array();
		foreach (headers_list() as $header) {
			if (preg_match('#^Set-Cookie: .+?=#', $header, $m)) {
				$flatten[$m[0]] = $header;
				header_remove('Set-Cookie');
			}
		}
		foreach (array_values($flatten) as $key => $header) {
			header($header, $key === 0);
		}
	}


	private function checkHeaders()
	{
		if (headers_sent($file, $line)) {
			throw new Nette\InvalidStateException('Cannot send header after HTTP headers have been sent' . ($file ? " (output started at $file:$line)." : '.'));
		} elseif (ob_get_length() && !array_filter(ob_get_status(TRUE), function($i) { return !$i['chunk_size']; })) {
			trigger_error('Possible problem: you are sending a HTTP header while already having some data in output buffer. Try OutputDebugger or start session earlier.', E_USER_NOTICE);
		}
	}

}
