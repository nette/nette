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
	/** @var array */
	protected $query = array();

	/** @var array */
	protected $post = array();

	/** @var array */
	protected $files = array();

	/** @var array */
	protected $cookies = array();

	/** @var UriScript  @see self::getUri() */
	protected $uri;

	/** @var Uri  @see self::getOriginalUri() */
	protected $originalUri;

	/** @var array  @see self::getHeaders() */
	protected $headers;



	public function __construct()
	{
		if (!empty($_GET)) {
			$this->query = $_GET;
		}
		if (!empty($_POST)) {
			$this->post = $_POST;
		}
		if (!empty($_COOKIE)) {
			$this->cookies = $_COOKIE;
		}
		if (get_magic_quotes_gpc()) { // remove fucking quotes
			$list = array(& $this->query, & $this->post, & $this->cookies);
			while (list($k, $v) = each($list)) {
				if (is_array($v)) {
					foreach ($v as $k2 => $foo) $list[] = & $list[$k][$k2];
				} else {
					$list[$k] = stripSlashes($v);
				}
			}
			unset($list, $k, $v, $k2, $foo);
		}

		$list = array();
		if (!empty($_FILES)) {
			foreach ($_FILES as $name => $v) {
				$v['@'] = & $this->files[$name];
				$list[] = $v;
			}
		}

		while (list(, $v) = each($list)) {
			if (!isset($v['name'])) continue;
			if (!is_array($v['name'])) {
				if (get_magic_quotes_gpc()) {
					$v['name'] = stripSlashes($v['name']);
				}
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
	}



	/**
	 * Returns URL object.
	 * @param  bool
	 * @return UriScript
	 */
	final public function getUri($clone = TRUE)
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
	final public function getOriginalUri($clone = TRUE)
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
	 * Returns variable provided to the script via URL query ($_GET).
	 * If no key is passed, returns the entire array.
	 * @param  string key
	 * @param  mixed  default value
	 * @return mixed
	 */
	final public function getQuery($key = NULL, $default = NULL)
	{
		if (func_num_args() === 0) {
			return $this->query;

		} elseif (isset($this->query[$key])) {
			return $this->query[$key];

		} else {
			return $default;
		}
	}



	/**
	 * Returns variable provided to the script via POST method ($_POST).
	 * If no key is passed, returns the entire array.
	 * @param  string key
	 * @param  mixed  default value
	 * @return mixed
	 */
	final public function getPost($key = NULL, $default = NULL)
	{
		if (func_num_args() === 0) {
			return $this->post;

		} elseif (isset($this->post[$key])) {
			return $this->post[$key];

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
	 * @param  string key (or more keys)
	 * @return HttpUploadedFile
	 */
	final public function getFile($key)
	{
		$var = $this->files;
		foreach (func_get_args() as $k) {
			if (is_array($var) && isset($var[$k])) {
				$var = $var[$k];
			} else {
				return NULL;
			}
		}
		return $var;
	}



	/**
	 * Returns uploaded files.
	 * @return array
	 */
	final public function getFiles()
	{
		return $this->files;
	}



	/**
	 * Returns variable provided to the script via HTTP cookies.
	 * @param  string key
	 * @param  mixed  default value
	 * @return mixed
	 */
	final public function getCookie($key, $default = NULL)
	{
		if (func_num_args() === 0) {
			return $this->cookies;

		} elseif (isset($this->cookies[$key])) {
			return $this->cookies[$key];

		} else {
			return $default;
		}
	}



	/**
	 * Returns variables provided to the script via HTTP cookies.
	 * @return array
	 */
	final public function getCookies()
	{
		return $this->cookies;
	}



	/**
	 * Return the value of the HTTP header. Pass the header name as the
	 * plain, HTTP-specified header name (e.g. 'Accept-Encoding').
	 * @param  string
	 * @param  mixed
	 * @return mixed
	 */
	final public function getHeader($header, $default = NULL)
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
	final public function getReferer()
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
		return $this->getHeader('X-Requested-With') === 'XMLHttpRequest';
	}



	/**
	 * Returns the IP or host address of the remote client.
	 * @param  bool  return host name?
	 * @return string
	 */
	public function getRemoteAddress($dns = FALSE)
	{
		if (!isset($_SERVER['REMOTE_ADDR'])) {
			return NULL;
		}

		if ($dns && !isset($_SERVER['REMOTE_HOST'])) {
			$_SERVER['REMOTE_HOST'] = getHostByAddr($_SERVER['REMOTE_ADDR']);
		}

		return $dns ? $_SERVER['REMOTE_HOST'] : $_SERVER['REMOTE_ADDR'];
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

}
