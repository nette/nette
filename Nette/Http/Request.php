<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Http;

use Nette;


/**
 * HttpRequest provides access scheme for request sent via HTTP.
 *
 * @author     David Grudl
 *
 * @property-read UrlScript $url
 * @property-read mixed $query
 * @property-read bool $post
 * @property-read array $files
 * @property-read array $cookies
 * @property-read string $method
 * @property-read array $headers
 * @property-read Url|NULL $referer
 * @property-read bool $secured
 * @property-read bool $ajax
 * @property-read string $remoteAddress
 * @property-read string $remoteHost
 * @property-read string $rawBody
 */
class Request extends Nette\Object implements IRequest
{
	/** @var string */
	private $method;

	/** @var UrlScript */
	private $url;

	/** @var array */
	private $query;

	/** @var array */
	private $post;

	/** @var array */
	private $files;

	/** @var array */
	private $cookies;

	/** @var array */
	private $headers;

	/** @var string */
	private $remoteAddress;

	/** @var string */
	private $remoteHost;

	/** @var string */
	private $rawBody;


	public function __construct(UrlScript $url, $query = NULL, $post = NULL, $files = NULL, $cookies = NULL,
		$headers = NULL, $method = NULL, $remoteAddress = NULL, $remoteHost = NULL)
	{
		$this->url = $url;
		if ($query === NULL) {
			parse_str($url->query, $this->query);
		} else {
			$this->query = (array) $query;
		}
		$this->post = (array) $post;
		$this->files = (array) $files;
		$this->cookies = (array) $cookies;
		$this->headers = (array) $headers;
		$this->method = $method;
		$this->remoteAddress = $remoteAddress;
		$this->remoteHost = $remoteHost;
	}


	/**
	 * Returns URL object.
	 * @return UrlScript
	 */
	public function getUrl()
	{
		return $this->url;
	}


	/********************* query, post, files & cookies ****************d*g**/


	/**
	 * Returns variable provided to the script via URL query ($_GET).
	 * If no key is passed, returns the entire array.
	 * @param  string key
	 * @param  mixed  default value
	 * @return mixed
	 */
	public function getQuery($key = NULL, $default = NULL)
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
	public function getPost($key = NULL, $default = NULL)
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
	 * Returns uploaded file.
	 * @param  string key (or more keys)
	 * @return FileUpload
	 */
	public function getFile($key)
	{
		return Nette\Utils\Arrays::get($this->files, func_get_args(), NULL);
	}


	/**
	 * Returns uploaded files.
	 * @return array
	 */
	public function getFiles()
	{
		return $this->files;
	}


	/**
	 * Returns variable provided to the script via HTTP cookies.
	 * @param  string key
	 * @param  mixed  default value
	 * @return mixed
	 */
	public function getCookie($key, $default = NULL)
	{
		return isset($this->cookies[$key]) ? $this->cookies[$key] : $default;
	}


	/**
	 * Returns variables provided to the script via HTTP cookies.
	 * @return array
	 */
	public function getCookies()
	{
		return $this->cookies;
	}


	/********************* method & headers ****************d*g**/


	/**
	 * Returns HTTP request method (GET, POST, HEAD, PUT, ...). The method is case-sensitive.
	 * @return string
	 */
	public function getMethod()
	{
		return $this->method;
	}


	/**
	 * Checks if the request method is the given one.
	 * @param  string
	 * @return bool
	 */
	public function isMethod($method)
	{
		return strcasecmp($this->method, $method) === 0;
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
	public function getHeader($header, $default = NULL)
	{
		$header = strtolower($header);
		if (isset($this->headers[$header])) {
			return $this->headers[$header];
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
		return $this->headers;
	}


	/**
	 * Returns referrer.
	 * @return Url|NULL
	 */
	public function getReferer()
	{
		return isset($this->headers['referer']) ? new Url($this->headers['referer']) : NULL;
	}


	/**
	 * Is the request is sent via secure channel (https).
	 * @return bool
	 */
	public function isSecured()
	{
		return $this->url->scheme === 'https';
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
		return $this->remoteAddress;
	}


	/**
	 * Returns the host of the remote client.
	 * @return string
	 */
	public function getRemoteHost()
	{
		if (!$this->remoteHost) {
			$this->remoteHost = $this->remoteAddress ? getHostByAddr($this->remoteAddress) : NULL;
		}
		return $this->remoteHost;
	}


	/**
	 * Returns raw content of HTTP request body.
	 * @return string
	 */
	public function getRawBody()
	{
		if (PHP_VERSION_ID >= 50600) {
			return file_get_contents('php://input');

		} elseif ($this->rawBody === NULL) { // can be read only once in PHP < 5.6
			$this->rawBody = (string) file_get_contents('php://input');
		}

		return $this->rawBody;
	}


	/**
	 * Parse Accept-Language header and returns prefered language.
	 * @param  array   Supported languages
	 * @return string|null
	 */
	public function detectLanguage(array $langs)
	{
		$header = $this->getHeader('Accept-Language');
		if (!$header) {
			return NULL;
		}

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
