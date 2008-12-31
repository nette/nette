<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2009 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Web
 * @version    $Id$
 */

/*namespace Nette\Web;*/



require_once dirname(__FILE__) . '/../Object.php';

require_once dirname(__FILE__) . '/../Web/IHttpResponse.php';



/**
 * HttpResponse class.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @package    Nette\Web
 */
final class HttpResponse extends /*Nette\*/Object implements IHttpResponse
{
	/** @var bool  Send invisible garbage for IE 6? */
	private static $fixIE = TRUE;

	/** @var string The domain in which the cookie will be available */
	public $cookieDomain = '';

	/** @var string The path in which the cookie will be available */
	public $cookiePath = '';

	/** @var string The path in which the cookie will be available */
	public $cookieSecure = FALSE;

	/** @var int HTTP response code */
	private $code = self::S200_OK;



	/**
	 * Sets HTTP response code.
	 * @param  int
	 * @return bool
	 * @throws \InvalidArgumentException  if code is invalid
	 * @throws \InvalidStateException  if HTTP headers have been sent
	 */
	public function setCode($code)
	{
		$code = (int) $code;

		static $allowed = array(
			200=>1, 201=>1, 202=>1, 203=>1, 204=>1, 205=>1, 206=>1,
			300=>1, 301=>1, 302=>1, 303=>1, 304=>1, 307=>1,
			400=>1, 401=>1, 403=>1, 404=>1, 406=>1, 408=>1, 410=>1, 412=>1, 415=>1, 416=>1,
			500=>1, 501=>1, 503=>1, 505=>1
		);

		if (!isset($allowed[$code])) {
			throw new /*\*/InvalidArgumentException("Bad HTTP response '$code'.");

		} elseif (headers_sent($file, $line)) {
			throw new /*\*/InvalidStateException("Cannot set HTTP code after HTTP headers have been sent" . ($file ? " (output started at $file:$line)." : "."));

		} else {
			$this->code = $code;
			$protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
			header($protocol . ' ' . $code, TRUE, $code);
			return TRUE;
		}
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
	 * @return void
	 * @throws \InvalidStateException  if HTTP headers have been sent
	 */
	public function setHeader($name, $value, $replace = TRUE)
	{
		if (headers_sent($file, $line)) {
			throw new /*\*/InvalidStateException("Cannot send header after HTTP headers have been sent" . ($file ? " (output started at $file:$line)." : "."));
		}

		header($name . ': ' . $value, $replace, $this->code);
	}



	/**
	 * Adds HTTP header.
	 * @param  string  header name
	 * @param  string  header value
	 * @return void
	 * @throws \InvalidStateException  if HTTP headers have been sent
	 */
	public function addHeader($name, $value)
	{
		if (headers_sent($file, $line)) {
			throw new /*\*/InvalidStateException("Cannot send header after HTTP headers have been sent" . ($file ? " (output started at $file:$line)." : "."));
		}

		header($name . ': ' . $value, FALSE, $this->code);
	}



	/**
	 * Sends a Content-type HTTP header.
	 * @param  string  mime-type
	 * @param  string  charset
	 * @return void
	 * @throws \InvalidStateException  if HTTP headers have been sent
	 */
	public function setContentType($type, $charset = NULL)
	{
		$this->setHeader('Content-Type', $type . ($charset ? '; charset=' . $charset : ''));
	}



	/**
	 * Redirects to a new URL. Note: call exit() after it.
	 * @param  string  URL
	 * @param  int     HTTP code
	 * @return void
	 * @throws \InvalidStateException  if HTTP headers have been sent
	 */
	public function redirect($url, $code = self::S302_FOUND)
	{
		$this->setCode($code);
		$this->setHeader('Location', $url);
		$url = htmlSpecialChars($url);
		echo "<h1>Redirect</h1>\n\n<p><a href=\"$url\">Please click here to continue</a>.</p>";
	}



	/**
	 * Sets the number of seconds before a page cached on a browser expires.
	 * @param  int  timestamp or number of seconds
	 * @return void
	 * @throws \InvalidStateException  if HTTP headers have been sent
	 */
	public function expire($time)
	{
		if ($time > 0) {
			if ($time <= /*Nette\*/Tools::YEAR) {
				$time += time();
			}
			$this->setHeader('Cache-Control', 'max-age=' . ($time - time()). ',must-revalidate');
			$this->setHeader('Expires', self::date($time));

		} else { // no cache
			$this->setHeader('Expires', 'Mon, 23 Jan 1978 10:00:00 GMT');
			$this->setHeader('Cache-Control', 's-maxage=0, max-age=0, must-revalidate');
		}
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
	 * Returns a list of headers to sent.
	 * @return array
	 */
	public function getHeaders()
	{
		$headers = array();
		foreach (headers_list() as $header) {
			$a = strpos($header, ':');
			$headers[substr($header, 0, $a)] = substr($header, $a + 2);
		}
		return $headers;
	}



	/**
	 * Returns HTTP valid date format.
	 * @param  int  timestamp
	 * @return string
	 */
	public static function date($time = NULL)
	{
		return gmdate('D, d M Y H:i:s \G\M\T', $time === NULL ? time() : $time);
	}



	/**
	 * Enables compression. (warning: may not work)
	 * @return bool
	 */
	public function enableCompression()
	{
		if (headers_sent()) return FALSE;

		$headers = $this->getHeaders();
		if (isset($headers['Content-Encoding'])) {
			return FALSE; // called twice
		}

		$ok = ob_gzhandler('', PHP_OUTPUT_HANDLER_START);
		if ($ok === FALSE) {
			return FALSE; // not allowed
		}

		if (function_exists('ini_set')) {
			ini_set('zlib.output_compression', 'Off');
			ini_set('zlib.output_compression_level', '6');
		}
		ob_start('ob_gzhandler');
		return TRUE;
	}



	/**
	 * @return void
	 */
	public function __destruct()
	{
		if (self::$fixIE) {
			// Sends invisible garbage for IE.
			if (!isset($_SERVER['HTTP_USER_AGENT']) || strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE ') === FALSE) return;
			if (!in_array($this->code, array(400, 403, 404, 405, 406, 408, 409, 410, 500, 501, 505), TRUE)) return;
			$headers = $this->getHeaders();
			if (isset($headers['Content-Type']) && $headers['Content-Type'] !== 'text/html') return;
			$s = " \t\r\n";
			for ($i = 2e3; $i; $i--) echo $s{rand(0, 3)};
			self::$fixIE = FALSE;
		}
	}



	/**
	 * Sends a cookie.
	 * @param  string name of the cookie
	 * @param  string value
	 * @param  int expiration as unix timestamp or number of seconds; Value 0 means "until the browser is closed"
	 * @param  string
	 * @param  string
	 * @param  bool
	 * @return void
	 * @throws \InvalidStateException  if HTTP headers have been sent
	 */
	public function setCookie($name, $value, $expire, $path = NULL, $domain = NULL, $secure = NULL)
	{
		if (headers_sent($file, $line)) {
			throw new /*\*/InvalidStateException("Cannot set cookie after HTTP headers have been sent" . ($file ? " (output started at $file:$line)." : "."));
		}

		setcookie(
			$name,
			$value,
			$expire > 0 && $expire <= /*Nette\*/Tools::YEAR ? $expire + time() : $expire,
			$path === NULL ? $this->cookiePath : (string) $path,
			$domain === NULL ? $this->cookieDomain : (string) $domain, //  . '; httponly'
			$secure === NULL ? $this->cookieSecure : (bool) $secure,
			TRUE // added in PHP 5.2.0.
		);
	}



	/**
	 * Deletes a cookie.
	 * @param  string name of the cookie.
	 * @param  string
	 * @param  string
	 * @param  bool
	 * @return void
	 * @throws \InvalidStateException  if HTTP headers have been sent
	 */
	public function deleteCookie($name, $path = NULL, $domain = NULL, $secure = NULL)
	{
		if (headers_sent($file, $line)) {
			throw new /*\*/InvalidStateException("Cannot delete cookie after HTTP headers have been sent" . ($file ? " (output started at $file:$line)." : "."));
		}

		setcookie(
			$name,
			FALSE,
			254400000,
			$path === NULL ? $this->cookiePath : (string) $path,
			$domain === NULL ? $this->cookieDomain : (string) $domain, //  . '; httponly'
			$secure === NULL ? $this->cookieSecure : (bool) $secure,
			TRUE // added in PHP 5.2.0.
		);
	}

}
