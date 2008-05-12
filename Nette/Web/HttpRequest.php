<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com/
 *
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com/
 * @category   Nette
 * @package    Nette::Web
 */

/*namespace Nette::Web;*/


require_once dirname(__FILE__) . '/../Object.php';

require_once dirname(__FILE__) . '/../Web/IHttpRequest.php';



/**
 * HttpRequest provides access scheme for request sent via HTTP.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Web
 * @version    $Revision$ $Date$
 */
class HttpRequest extends /*Nette::*/Object implements IHttpRequest
{
	/** @var Uri  @see self::getUri() */
	private $uri;

	/** @var Uri  @see self::getOriginalUri() */
	private $originalUri;

	/** @var array  @see self::getHeaders() */
	private $headers;

	/** @var bool  @see self::isLocal() */
	private $isLocal;



	/**
	 * Sets URL object
	 * @param  Uri
	 * @return void
	 */
	public function setUri(Uri $uri)
	{
		$this->uri = $this->originalUri = $uri;
	}



	/**
	 * Returns URL object
	 * @return Uri
	 */
	public function getUri()
	{
		if ($this->uri === NULL) {
			$this->detectUri();
		}
		return clone $this->uri;
	}



	/**
	 * Returns URL object
	 * @return Uri
	 */
	public function getOriginalUri()
	{
		if ($this->originalUri === NULL) {
			$this->detectUri();
		}
		return clone $this->originalUri;
	}



	/**
	 * Detects uri, base path and script path of the request.
	 * @return void
	 */
	protected function detectUri()
	{
		$origUri = $this->originalUri = new Uri;
		$origUri->scheme = $this->isSecured() ? 'https' : 'http';
		$origUri->user = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '';
		$origUri->pass = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';
		$origUri->port = isset($_SERVER['SERVER_PORT']) ? (int) $_SERVER['SERVER_PORT'] : 0;
		$origUri->query = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';

		// path
		if (isset($_SERVER['REQUEST_URI'])) { // Apache, IIS 6.0
			$origUri->path = (string) strtok($_SERVER['REQUEST_URI'], '?');
		} elseif (isset($_SERVER['ORIG_PATH_INFO'])) { // IIS 5.0 (PHP as CGI ?)
			$origUri->path = $_SERVER['ORIG_PATH_INFO'];
		}

		// host
		if (isset($_SERVER['HTTP_HOST'])) {
			$origUri->host = (string) strtok($_SERVER['HTTP_HOST'], ':');
		} elseif (isset($_SERVER['SERVER_NAME'])) {
			$origUri->host = (string) strtok($_SERVER['SERVER_NAME'], ':');
		}

		// normalized uri
		$uri = $this->uri = clone $origUri;
		// TODO: add ability to use URI filters
		$uri->canonicalize();


		// detect base URI-path - inspired by Zend Framework (c) Zend Technologies USA Inc. (http://www.zend.com), new BSD license
		$filename = basename($_SERVER['SCRIPT_FILENAME']);

		if (basename($_SERVER['SCRIPT_NAME']) === $filename) {
			$baseScript = $_SERVER['SCRIPT_NAME'];
		} elseif (basename($_SERVER['PHP_SELF']) === $filename) {
			$baseScript = $_SERVER['PHP_SELF'];
		} elseif (isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $filename) {
			$baseScript = $_SERVER['ORIG_SCRIPT_NAME']; // 1and1 shared hosting compatibility
		} else {
			// Backtrack up the script_filename to find the portion matching php_self
			$path = $_SERVER['PHP_SELF'];
			$segs = explode('/', trim($_SERVER['SCRIPT_FILENAME'], '/'));
			$segs = array_reverse($segs);
			$index = 0;
			$last = count($segs);
			$baseScript = '';
			do {
				$seg = $segs[$index];
				$baseScript = '/' . $seg . $baseScript;
				$index++;
			} while (($last > $index) && (FALSE !== ($pos = strpos($path, $baseScript))) && (0 != $pos));
		}

		// Does the baseScript have anything in common with the request_uri?
		$basePath = substr($baseScript, 0, strrpos($baseScript, '/')); // do not use dirinfo!

		if (strpos($uri->path, $baseScript) === 0) {
			// full $baseScript matches
			$uri->baseScript = $baseScript;
			$uri->basePath = $basePath;

		} elseif (strpos($uri->path, $basePath . '/') === 0) {
			// directory portion of $baseScript matches
			$uri->baseScript = $uri->basePath = $basePath;

		} elseif (strpos($uri->path, basename($baseScript)) === FALSE) {
			// no match whatsoever; set it blank
			$uri->baseScript = '/';
			$uri->basePath = '/';

		} elseif ((strlen($uri->path) >= strlen($baseScript))
			&& ((false !== ($pos = strpos($uri->path, $baseScript))) && ($pos !== 0))) {
			// If using mod_rewrite or ISAPI_Rewrite strip the script filename
			// out of baseScript. $pos !== 0 makes sure it is not matching a value
			// from PATH_INFO or QUERY_STRING
			$uri->baseScript = substr($uri->path, 0, $pos + strlen($baseScript));
			// do not use dirinfo!
			$uri->basePath = substr($baseScript, 0, strrpos($baseScript, '/') + 1);

		} else {
			$uri->baseScript = rtrim($baseScript, '/');
			$uri->basePath = $basePath;
		}
	}



	/**
	 * Returns HTTP request method (GET, POST, HEAD, PUT, ...).
	 * @return string
	 */
	public function getMethod()
	{
		return isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : NULL;
	}



	/**
	 * Returns variable provided to the script via URL query ($_GET).
	 * If no $key is passed, returns the entire array.
	 *
	 * @param  string
	 * @param  mixed  default value to use if key not found
	 * @return mixed
	 */
	public function getQuery($key = NULL, $default = NULL)
	{
		if ($key === NULL) {
			return $_GET;

		} elseif (isset($_GET[$key])) {
			return $_GET[$key];

		} else {
			return $default;
		}
	}



	/**
	 * Returns variable provided to the script via POST method ($_POST).
	 * If no $key is passed, returns the entire array.
	 *
	 * @param  string
	 * @param  mixed  default value to use if key not found
	 * @return mixed
	 */
	public function getPost($key = NULL, $default = NULL)
	{
		if ($key === NULL) {
			return $_POST;

		} elseif (isset($_POST[$key])) {
			return $_POST[$key];

		} else {
			return $default;
		}
	}



	/**
	 * Returns HTTP POST data in raw format (only for "application/x-www-form-urlencoded").
	 * @return string
	 */
	public function getPostRaw()
	{
		return file_get_contents('php://input');
	}



	/**
	 * Returns uploaded file.
	 *
	 * @param  string
	 * @return array|NULL
	 */
	public function getFile($key)
	{
		if (isset($_FILES[$key])) {
			return $_FILES[$key];
		} else {
			return NULL;
		}
	}



	/**
	 * Returns all uploaded files.
	 * @return array
	 */
	public function getFiles()
	{
		return $_FILES;
	}



	/**
	 * Returns variable provided to the script via HTTP cookies.
	 *
	 * @param  string
	 * @param  mixed  default value to use if key not found
	 * @return mixed
	 */
	public function getCookie($key, $default = NULL)
	{
		if (isset($_COOKIE[$key])) {
			return $_COOKIE[$key];
		} else {
			return $default;
		}
	}



	/**
	 * Returns all HTTP cookies.
	 * @return array
	 */
	public function getCookies()
	{
		return $_COOKIE;
	}



	/**
	 * Return the value of the HTTP header. Pass the header name as the.
	 * plain, HTTP-specified header name. Ex.: Ask for 'Accept' to get the
	 * Accept header, 'Accept-Encoding' to get the Accept-Encoding header.
	 *
	 * @param  string
	 * @param  mixed
	 * @return string|NULL  single HTTP header value
	 */
	public function getHeader($header, $default = NULL)
	{
		$header = strtolower($header);
		$headers = $this->getHeaders();
		if (isset($headers[$header])) {
			return $headers[$header];
		} else {
			return $default;
		}
	}



	/**
	 * Returns all HTTP headers
	 * @return array
	 */
	public function getHeaders()
	{
		if ($this->headers === NULL) {
			// lazy initialization
			if (function_exists('apache_request_headers')) {
				$this->headers = array_change_key_case(apache_request_headers(), CASE_LOWER);
			} else {
				$this->headers = array();
				foreach ($_SERVER as $k => $v) {
					if (strncmp($k, 'HTTP_', 5) == 0) {
						$this->headers[ strtr(strtolower(substr($k, 5)), '_', '-') ] = $v;
					}
				}
			}
		}
		return $this->headers;
	}



	/**
	 * Returns referrer
	 * @return Uri|NULL
	 */
	public function getReferer()
	{
		$uri = self::getHeader('referer');
		return $uri ? new Uri($uri) : NULL;
	}



	/**
	 * Is the request is sent via secure channel (https).
	 * @return boolean
	 */
	public function isSecured()
	{
		return isset($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'], 'off');
	}



	/**
	 * Is Ajax request?
	 * @return boolean
	 */
	public function isAjax()
	{
		return ($this->getMethod() === 'POST') && ($this->getHeader('X-Requested-With') === 'XMLHttpRequest');
	}



	/**
	 * Is server is running on local host?
	 * @return boolean
	 */
	public function isLocal()
	{
		if ($this->isLocal === NULL) {
			$this->isLocal = FALSE;
			if (isset($_SERVER['REMOTE_ADDR'])) {
				$oct = explode('.', $_SERVER['REMOTE_ADDR']);
				$this->isLocal = (count($oct) === 4) && ($oct[0] === '10' || $oct[0] === '127' || ($oct[0] === '171' && $oct[1] > 15 && $oct[1] < 32)
					|| ($oct[0] === '169' && $oct[1] === '254') || ($oct[0] === '192' && $oct[1] === '168'));
			}
		}
		return $this->isLocal;
	}



	/**
	 * Parse Accept-Language header and returns prefered language.
	 * @param  array   Supported languages
	 * @return string
	 */
	public function detectLanguage(/*array*/ $langs)
	{
		$header = $this->getHeader('accept-language');
		if (!$header) return NULL;

		$s = strtolower($header);  // case insensitive
		$s = strtr($s, '_', '-');  // cs_CZ means cs-CZ
		rsort($langs);             // first more specific
		preg_match_all('#('.implode('|', $langs).')(?:-[^\s,;=]+)?\s*(?:;\s*q=([0-9.]+))?#', $s, $matches);

		if (!$matches[0]) {
			return NULL;
		}

		$max = 0;
		$lang = NULL;
		foreach ($matches[1] as $key => $value) {
			$q = $matches[2][$key] === '' ? 1.0 : (float) $matches[2][$key];
			if ($q > $max) {
				$max = $q; $lang = $value;
			}
		}

		return $lang;
	}



	/**
	 * Generates hash from all used IP & host names from request headers.
	 * @return string
	 */
	public function ipHash()
	{
		static $vars = array(
			'REMOTE_ADDR', 'HTTP_FROM', 'HTTP_CLIENT_IP', 'HTTP_VIA', 'HTTP_COMING_FROM', 'HTTP_X_COMING_FROM', 'HTTP_PROXY_CONNECTION',
			'HTTP_PROXY', 'HTTP_X_PROXY', 'HTTP_X_FORWARDED_FOR', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'HTTP_X_FORWARDED',
		);

		$ip = array();
		foreach ($vars as $var) {
			if (isset($_SERVER[$var])) {
				$ip[] = substr($_SERVER[$var], 0, 20);
			}
		}

		return sha1(implode('|', $ip));
	}



	/**
	 * Magic quotes remover.
	 * @param  array
	 * @return void
	 */
	public static function fuckingQuotes($list)
	{
		if (get_magic_quotes_gpc()) {
			while (list($k, $v) = each($list)) {
				if (is_array($v)) {
					foreach ($v as $k2 => $foo) $list[] = & $list[$k][$k2];
				} else {
					$list[$k] = stripSlashes($v);
				}
			}
		}
	}

}



// init: HttpRequest::fuckingQuotes(array(&$_GET, &$_POST, &$_COOKIE, &$_FILES));
