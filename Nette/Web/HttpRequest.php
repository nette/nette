<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette::Web
 * @version    $Id$
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
 */
class HttpRequest extends /*Nette::*/Object implements IHttpRequest
{
	/** @var Nette::Collections::Hashtable */
	private $query;

	/** @var Nette::Collections::Hashtable */
	private $post;

	/** @var Nette::Collections::Hashtable */
	private $files;

	/** @var Nette::Collections::Hashtable */
	private $cookies;

	/** @var UriScript  @see self::getUri() */
	private $uri;

	/** @var Uri  @see self::getOriginalUri() */
	private $originalUri;

	/** @var array  @see self::getHeaders() */
	private $headers;



	/**
	 * Returns URL object.
	 * @param  bool
	 * @return UriScript
	 */
	public function getUri($clone = TRUE)
	{
		if ($this->uri === NULL) {
			$this->detectUri();
		}
		return $clone ? clone $this->uri : $this->uri;
	}



	/**
	 * Returns URL object.
	 * @param  bool
	 * @return Uri
	 */
	public function getOriginalUri($clone = TRUE)
	{
		if ($this->originalUri === NULL) {
			$this->detectUri();
		}
		return $clone ? clone $this->originalUri : $this->originalUri;
	}



	/**
	 * Detects uri, base path and script path of the request.
	 * @return void
	 */
	protected function detectUri()
	{
		$uri = $this->uri = new UriScript;
		$origUri = $this->originalUri = new Uri;

		$uri->scheme = $origUri->scheme = $this->isSecured() ? 'https' : 'http';
		$uri->user = $origUri->user = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '';
		$uri->pass = $origUri->pass = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';
		$uri->port = $origUri->port = isset($_SERVER['SERVER_PORT']) ? (int) $_SERVER['SERVER_PORT'] : NULL;
		$uri->query = $origUri->query = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';

		// path
		if (isset($_SERVER['REQUEST_URI'])) { // Apache, IIS 6.0
			$uri->path = $origUri->path = (string) strtok($_SERVER['REQUEST_URI'], '?');
		} elseif (isset($_SERVER['ORIG_PATH_INFO'])) { // IIS 5.0 (PHP as CGI ?)
			$uri->path = $origUri->path = $_SERVER['ORIG_PATH_INFO'];
		}

		// host
		if (isset($_SERVER['HTTP_HOST'])) {
			$uri->host = $origUri->host = (string) strtok($_SERVER['HTTP_HOST'], ':');
		} elseif (isset($_SERVER['SERVER_NAME'])) {
			$uri->host = $origUri->host = (string) strtok($_SERVER['SERVER_NAME'], ':');
		}

		// normalized uri
		$uri->canonicalize();

		// detect base URI-path - inspired by Zend Framework (c) Zend Technologies USA Inc. (http://www.zend.com), new BSD license
		$filename = basename($_SERVER['SCRIPT_FILENAME']);

		if (basename($_SERVER['SCRIPT_NAME']) === $filename) {
			$scriptPath = rtrim($_SERVER['SCRIPT_NAME'], '/');

		} elseif (basename($_SERVER['PHP_SELF']) === $filename) {
			$scriptPath = $_SERVER['PHP_SELF'];

		} elseif (isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $filename) {
			$scriptPath = $_SERVER['ORIG_SCRIPT_NAME']; // 1and1 shared hosting compatibility

		} else {
			// Backtrack up the script_filename to find the portion matching php_self
			$path = $_SERVER['PHP_SELF'];
			$segs = explode('/', trim($_SERVER['SCRIPT_FILENAME'], '/'));
			$segs = array_reverse($segs);
			$index = 0;
			$last = count($segs);
			$scriptPath = '';
			do {
				$seg = $segs[$index];
				$scriptPath = '/' . $seg . $scriptPath;
				$index++;
			} while (($last > $index) && (FALSE !== ($pos = strpos($path, $scriptPath))) && (0 != $pos));
		}

		// Does the scriptPath have anything in common with the request_uri?
		if (strncmp($uri->path, $scriptPath, strlen($scriptPath)) === 0) {
			// whole $scriptPath in URL
			$uri->scriptPath = $scriptPath;

		} elseif (strncmp($uri->path, $scriptPath, strrpos($scriptPath, '/') + 1) === 0) {
			// directory portion of $scriptPath in URL
			$uri->scriptPath = substr($scriptPath, 0, strrpos($scriptPath, '/') + 1);

		} elseif (strpos($uri->path, basename($scriptPath)) === FALSE) {
			// no match whatsoever; set it blank
			$uri->scriptPath = '/';

		} elseif ((strlen($uri->path) >= strlen($scriptPath))
			&& ((FALSE !== ($pos = strpos($uri->path, $scriptPath))) && ($pos !== 0))) {
			// If using mod_rewrite or ISAPI_Rewrite strip the script filename
			// out of scriptPath. $pos !== 0 makes sure it is not matching a value
			// from PATH_INFO or QUERY_STRING
			$uri->scriptPath = substr($uri->path, 0, $pos + strlen($scriptPath));

		} else {
			$uri->scriptPath = $scriptPath;
		}
	}



	/**
	 * Returns HTTP request method (GET, POST, HEAD, PUT, ...). The method is case-sensitive.
	 * @return string
	 */
	public function getMethod()
	{
		return isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : NULL;
	}



	/**
	 * Returns variables provided to the script via URL query ($_GET).
	 * @return Nette::Collections::Hashtable
	 */
	public function getQuery()
	{
		if ($this->query === NULL) {
			$this->query = new /*Nette::Collections::*/Hashtable($_GET);
		}
		return $this->query;
	}



	/**
	 * Returns variables provided to the script via POST method ($_POST).
	 * @return Nette::Collections::Hashtable
	 */
	public function getPost()
	{
		if ($this->post === NULL) {
			$this->post = new /*Nette::Collections::*/Hashtable($_POST);
		}
		return $this->post;
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
	 * Returns uploaded files.
	 * @return Nette::Collections::Hashtable
	 */
	public function getFiles()
	{
		if ($this->files === NULL) {
			$dest = array();
			$list = array();
			foreach ($_FILES as $name => $v) {
				$v['@'] = & $dest[$name];
				$list[] = $v;
			}

			while (list(, $v) = each($list)) {
				if (!is_array($v['name'])) {
					$v['@'] = new HttpUploadedFile($v);
					continue;
				}
				foreach ($v['name'] as $k => $foo) {
					$list[] = array(
						'name' => $v['name'][$k],
						'type' => $v['type'][$k],
						'size' => $v['size'][$k],
						'tmp_name' => $v['tmp_name'][$k],
						'error' => $v['error'][$k],
						'@' => & $v['@'][$k],
					);
				}
			}
			$this->files = new /*Nette::Collections::*/Hashtable($dest);
		}
		return $this->files;
	}



	/**
	 * Returns variables provided to the script via HTTP cookies.
	 * @return Nette::Collections::Hashtable
	 */
	public function getCookies()
	{
		if ($this->cookies === NULL) {
			$this->cookies = new /*Nette::Collections::*/Hashtable($_COOKIE);
		}
		return $this->cookies;
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
	 * Returns all HTTP headers.
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
	 * Returns referrer.
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
	 * Is AJAX request?
	 * @return boolean
	 */
	public function isAjax()
	{
		return ($this->getMethod() === 'POST') && ($this->getHeader('X-Requested-With') === 'XMLHttpRequest');
	}



	/**
	 * Parse Accept-Language header and returns prefered language.
	 * @param  array   Supported languages
	 * @return string
	 */
	public function detectLanguage(array $langs)
	{
		$header = $this->getHeader('accept-language');
		if (!$header) return NULL;

		$s = strtolower($header);  // case insensitive
		$s = strtr($s, '_', '-');  // cs_CZ means cs-CZ
		rsort($langs);             // first more specific
		preg_match_all('#(' . implode('|', $langs) . ')(?:-[^\s,;=]+)?\s*(?:;\s*q=([0-9.]+))?#', $s, $matches);

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
