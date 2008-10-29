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
	protected $query;

	/** @var array */
	protected $post;

	/** @var array */
	protected $files;

	/** @var array */
	protected $cookies;

	/** @var UriScript {@link HttpRequest::getUri()} */
	protected $uri;

	/** @var Uri  {@link HttpRequest::getOriginalUri()} */
	protected $originalUri;

	/** @var array  {@link HttpRequest::getHeaders()} */
	protected $headers;

	/** @var string */
	protected $encoding;



	/********************* URI ****************d*g**/



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



	/********************* query, post, files & cookies ****************d*g**/



	/**
	 * Returns variable provided to the script via URL query ($_GET).
	 * If no key is passed, returns the entire array.
	 * @param  string key
	 * @param  mixed  default value
	 * @return mixed
	 */
	final public function getQuery($key = NULL, $default = NULL)
	{
		if ($this->query === NULL) {
			$this->initialize();
		}

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
		if ($this->post === NULL) {
			$this->initialize();
		}

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
		if ($this->files === NULL) {
			$this->initialize();
		}

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
		if ($this->files === NULL) {
			$this->initialize();
		}

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
		if ($this->cookies === NULL) {
			$this->initialize();
		}

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
		if ($this->cookies === NULL) {
			$this->initialize();
		}

		return $this->cookies;
	}



	/**
	 * Recursively converts and checks encoding.
	 * @param  array
	 * @param  string
	 * @return void
	 */
	public function setEncoding($encoding)
	{
		if (strcasecmp($encoding, $this->encoding)) {
			$this->encoding = $encoding;
			$this->query = $this->post = $this->cookies = NULL; // reinitialization required
		}
	}



	/**
	 * Initializes $this->query, $this->files, $this->cookies and $this->files arrays
	 * @return void
	 */
	public function initialize()
	{
		$this->query = $this->post = $this->files = $this->cookies = array();

		if (!empty($_GET)) {
			$this->query = $_GET;
		}
		if (!empty($_POST)) {
			$this->post = $_POST;
		}
		if (!empty($_COOKIE)) {
			$this->cookies = $_COOKIE;
		}

		$gpc = (bool) get_magic_quotes_gpc();
		$enc = (bool) $this->encoding;
		$old = error_reporting(0);


		// remove fucking quotes and check (and optionally convert) encoding
		if ($gpc || $enc) {
			$utf = strcasecmp($this->encoding, 'UTF-8') === 0;
			$list = array(& $this->query, & $this->post, & $this->cookies);
			while (list($key, $val) = each($list)) {
				foreach ($val as $k => $v) {
					unset($list[$key][$k]);

					if ($gpc) {
						$k = stripslashes($k);
					}

					if ($enc && is_string($k) && $k !== iconv('UTF-8', 'UTF-8//IGNORE', $k)) {
						// invalid key -> ignore

					} elseif (is_array($v)) {
						$list[$key][$k] = $v;
						$list[] = & $list[$key][$k];

					} else {
						if ($gpc) {
							$v = stripSlashes($v);
						}
						if ($enc) {
							if ($utf) {
								$v = iconv('UTF-8', 'UTF-8//IGNORE', $v);

							} else {
								if ($v != iconv('UTF-8', 'UTF-8//IGNORE', $v)) {
									$v = iconv($this->encoding, 'UTF-8//IGNORE', $v);
								}
								$v = html_entity_decode($v, ENT_NOQUOTES, 'UTF-8');
							}
						}
						$list[$key][$k] = $v;
					}
				}
			}
			unset($list, $key, $val, $k, $v);
		}


		// structure $files and create HttpUploadedFile objects
		$list = array();
		if (!empty($_FILES)) {
			foreach ($_FILES as $k => $v) {
				if ($enc && is_string($k) && $k !== iconv('UTF-8', 'UTF-8//IGNORE', $k)) continue;
				$v['@'] = & $this->files[$k];
				$list[] = $v;
			}
		}

		while (list(, $v) = each($list)) {
			if (!isset($v['name'])) {
				continue;

			} elseif (!is_array($v['name'])) {
				if ($gpc) {
					$v['name'] = stripSlashes($v['name']);
				}
				if ($enc) {
					$v['name'] = iconv('UTF-8', 'UTF-8//IGNORE', $v['name']);
				}
				$v['@'] = new HttpUploadedFile($v);
				continue;
			}

			foreach ($v['name'] as $k => $foo) {
				if ($enc && is_string($k) && $k !== iconv('UTF-8', 'UTF-8//IGNORE', $k)) continue;
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

		error_reporting($old);
	}



	/********************* method & headers ****************d*g**/



	/**
	 * Returns HTTP request method (GET, POST, HEAD, PUT, ...). The method is case-sensitive.
	 * @return string
	 */
	public function getMethod()
	{
		return isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : NULL;
	}



	/**
	 * Checks if the request method is the given one.
	 * @param  string
	 * @return bool
	 */
	public function isMethod($method)
	{
		return isset($_SERVER['REQUEST_METHOD']) ? strcasecmp($_SERVER['REQUEST_METHOD'], $method) === 0 : FALSE;
	}



	/**
	 * Checks if the request method is POST.
	 * @return bool
	 */
	public function isPost()
	{
		return $this->isMethod('POST');
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
	 * @return bool
	 */
	public function isSecured()
	{
		return isset($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'], 'off');
	}



	/**
	 * Is AJAX request?
	 * @return bool
	 */
	public function isAjax()
	{
		return $this->getHeader('X-Requested-With') === 'XMLHttpRequest';
	}



	/**
	 * Returns the IP address of the remote client.
	 * @return string
	 */
	public function getRemoteAddress()
	{
		return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : NULL;
	}



	/**
	 * Returns the host of the remote client.
	 * @return string
	 */
	public function getRemoteHost()
	{
		if (!isset($_SERVER['REMOTE_HOST'])) {
			if (!isset($_SERVER['REMOTE_ADDR'])) {
				return NULL;
			}
			$_SERVER['REMOTE_HOST'] = getHostByAddr($_SERVER['REMOTE_ADDR']);
		}

		return $_SERVER['REMOTE_HOST'];
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
