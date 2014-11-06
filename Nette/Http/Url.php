<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Http;

use Nette;


/**
 * URI Syntax (RFC 3986).
 *
 * <pre>
 * scheme  user  password  host  port  basePath   relativeUrl
 *   |      |      |        |      |    |             |
 * /--\   /--\ /------\ /-------\ /--\/--\/----------------------------\
 * http://john:x0y17575@nette.org:8042/en/manual.php?name=param#fragment  <-- absoluteUrl
 *        \__________________________/\____________/^\________/^\______/
 *                     |                     |           |         |
 *                 authority               path        query    fragment
 * </pre>
 *
 * - authority:   [user[:password]@]host[:port]
 * - hostUrl:     http://user:password@nette.org:8042
 * - basePath:    /en/ (everything before relative URI not including the script name)
 * - baseUrl:     http://user:password@nette.org:8042/en/
 * - relativeUrl: manual.php
 *
 * @author     David Grudl
 *
 * @property   string $scheme
 * @property   string $user
 * @property   string $password
 * @property   string $host
 * @property   string $port
 * @property   string $path
 * @property   string $query
 * @property   string $fragment
 * @property-read string $absoluteUrl
 * @property-read string $authority
 * @property-read string $hostUrl
 * @property-read string $basePath
 * @property-read string $baseUrl
 * @property-read string $relativeUrl
 */
class Url extends Nette\Object
{
	/** @var array */
	public static $defaultPorts = array(
		'http' => 80,
		'https' => 443,
		'ftp' => 21,
		'news' => 119,
		'nntp' => 119,
	);

	/** @var string */
	private $scheme = '';

	/** @var string */
	private $user = '';

	/** @var string */
	private $pass = '';

	/** @var string */
	private $host = '';

	/** @var int */
	private $port = NULL;

	/** @var string */
	private $path = '';

	/** @var string */
	private $query = '';

	/** @var string */
	private $fragment = '';


	/**
	 * @param  string|self
	 * @throws Nette\InvalidArgumentException if URL is malformed
	 */
	public function __construct($url = NULL)
	{
		if (is_string($url)) {
			$parts = @parse_url($url); // @ - is escalated to exception
			if ($parts === FALSE) {
				throw new Nette\InvalidArgumentException("Malformed or unsupported URI '$url'.");
			}

			foreach ($parts as $key => $val) {
				$this->$key = $val;
			}

			if (!$this->port && isset(self::$defaultPorts[$this->scheme])) {
				$this->port = self::$defaultPorts[$this->scheme];
			}

			if ($this->path === '' && ($this->scheme === 'http' || $this->scheme === 'https')) {
				$this->path = '/';
			}

		} elseif ($url instanceof self) {
			foreach ($this as $key => $val) {
				$this->$key = $url->$key;
			}
		}
	}


	/**
	 * Sets the scheme part of URI.
	 * @param  string
	 * @return self
	 */
	public function setScheme($value)
	{
		$this->scheme = (string) $value;
		return $this;
	}


	/**
	 * Returns the scheme part of URI.
	 * @return string
	 */
	public function getScheme()
	{
		return $this->scheme;
	}


	/**
	 * Sets the user name part of URI.
	 * @param  string
	 * @return self
	 */
	public function setUser($value)
	{
		$this->user = (string) $value;
		return $this;
	}


	/**
	 * Returns the user name part of URI.
	 * @return string
	 */
	public function getUser()
	{
		return $this->user;
	}


	/**
	 * Sets the password part of URI.
	 * @param  string
	 * @return self
	 */
	public function setPassword($value)
	{
		$this->pass = (string) $value;
		return $this;
	}


	/**
	 * Returns the password part of URI.
	 * @return string
	 */
	public function getPassword()
	{
		return $this->pass;
	}


	/**
	 * Sets the host part of URI.
	 * @param  string
	 * @return self
	 */
	public function setHost($value)
	{
		$this->host = (string) $value;
		return $this;
	}


	/**
	 * Returns the host part of URI.
	 * @return string
	 */
	public function getHost()
	{
		return $this->host;
	}


	/**
	 * Sets the port part of URI.
	 * @param  string
	 * @return self
	 */
	public function setPort($value)
	{
		$this->port = (int) $value;
		return $this;
	}


	/**
	 * Returns the port part of URI.
	 * @return string
	 */
	public function getPort()
	{
		return $this->port;
	}


	/**
	 * Sets the path part of URI.
	 * @param  string
	 * @return self
	 */
	public function setPath($value)
	{
		$this->path = (string) $value;
		return $this;
	}


	/**
	 * Returns the path part of URI.
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}


	/**
	 * Sets the query part of URI.
	 * @param  string|array
	 * @return self
	 */
	public function setQuery($value)
	{
		$this->query = (string) (is_array($value) ? http_build_query($value, '', '&') : $value);
		return $this;
	}


	/**
	 * Appends the query part of URI.
	 * @param  string|array
	 * @return self
	 */
	public function appendQuery($value)
	{
		$value = (string) (is_array($value) ? http_build_query($value, '', '&') : $value);
		$this->query .= ($this->query === '' || $value === '') ? $value : '&' . $value;
		return $this;
	}


	/**
	 * Returns the query part of URI.
	 * @return string
	 */
	public function getQuery()
	{
		return $this->query;
	}


	/**
	 * @param string
	 * @param mixed
	 * @return mixed
	 */
	public function getQueryParameter($name, $default = NULL)
	{
		parse_str($this->query, $params);
		return isset($params[$name]) ? $params[$name] : $default;
	}


	/**
	 * @param string
	 * @param mixed NULL unsets the parameter
	 * @return self
	 */
	public function setQueryParameter($name, $value)
	{
		parse_str($this->query, $params);
		if ($value === NULL) {
			unset($params[$name]);
		} else {
			$params[$name] = $value;
		}
		$this->setQuery($params);
		return $this;
	}


	/**
	 * Sets the fragment part of URI.
	 * @param  string
	 * @return self
	 */
	public function setFragment($value)
	{
		$this->fragment = (string) $value;
		return $this;
	}


	/**
	 * Returns the fragment part of URI.
	 * @return string
	 */
	public function getFragment()
	{
		return $this->fragment;
	}


	/**
	 * Returns the entire URI including query string and fragment.
	 * @return string
	 */
	public function getAbsoluteUrl()
	{
		return $this->getHostUrl() . $this->path
			. ($this->query === '' ? '' : '?' . $this->query)
			. ($this->fragment === '' ? '' : '#' . $this->fragment);
	}


	/**
	 * Returns the [user[:pass]@]host[:port] part of URI.
	 * @return string
	 */
	public function getAuthority()
	{
		$authority = $this->host;
		if ($this->port && (!isset(self::$defaultPorts[$this->scheme]) || $this->port !== self::$defaultPorts[$this->scheme])) {
			$authority .= ':' . $this->port;
		}

		if ($this->user !== '' && $this->scheme !== 'http' && $this->scheme !== 'https') {
			$authority = $this->user . ($this->pass === '' ? '' : ':' . $this->pass) . '@' . $authority;
		}

		return $authority;
	}


	/**
	 * Returns the scheme and authority part of URI.
	 * @return string
	 */
	public function getHostUrl()
	{
		return ($this->scheme ? $this->scheme . ':' : '') . '//' . $this->getAuthority();
	}


	/**
	 * Returns the base-path.
	 * @return string
	 */
	public function getBasePath()
	{
		$pos = strrpos($this->path, '/');
		return $pos === FALSE ? '' : substr($this->path, 0, $pos + 1);
	}


	/**
	 * Returns the base-URI.
	 * @return string
	 */
	public function getBaseUrl()
	{
		return $this->getHostUrl() . $this->getBasePath();
	}


	/**
	 * Returns the relative-URI.
	 * @return string
	 */
	public function getRelativeUrl()
	{
		return (string) substr($this->getAbsoluteUrl(), strlen($this->getBaseUrl()));
	}


	/**
	 * URL comparison.
	 * @param  string|self
	 * @return bool
	 */
	public function isEqual($url)
	{
		$url = new self($url);
		parse_str($url->query, $query);
		sort($query);
		parse_str($this->query, $query2);
		sort($query2);
		$http = in_array($this->scheme, array('http', 'https'), TRUE);
		return $url->scheme === $this->scheme
			&& !strcasecmp(rawurldecode($url->host), rawurldecode($this->host))
			&& $url->port === $this->port
			&& ($http || rawurldecode($url->user) === rawurldecode($this->user))
			&& ($http || rawurldecode($url->pass) === rawurldecode($this->pass))
			&& self::unescape($url->path, '%/') === self::unescape($this->path, '%/')
			&& $query === $query2
			&& rawurldecode($url->fragment) === rawurldecode($this->fragment);
	}


	/**
	 * Transforms URL to canonical form.
	 * @return self
	 */
	public function canonicalize()
	{
		$this->path = $this->path === '' ? '/' : self::unescape($this->path, '%/');
		$this->host = strtolower(rawurldecode($this->host));
		$this->query = self::unescape(strtr($this->query, '+', ' '), '%&;=+');
		return $this;
	}


	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->getAbsoluteUrl();
	}


	/**
	 * Similar to rawurldecode, but preserve reserved chars encoded.
	 * @param  string to decode
	 * @param  string reserved characters
	 * @return string
	 */
	public static function unescape($s, $reserved = '%;/?:@&=+$,')
	{
		// reserved (@see RFC 2396) = ";" | "/" | "?" | ":" | "@" | "&" | "=" | "+" | "$" | ","
		// within a path segment, the characters "/", ";", "=", "?" are reserved
		// within a query component, the characters ";", "/", "?", ":", "@", "&", "=", "+", ",", "$" are reserved.
		preg_match_all('#(?<=%)[a-f0-9][a-f0-9]#i', $s, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
		foreach (array_reverse($matches) as $match) {
			$ch = chr(hexdec($match[0][0]));
			if (strpos($reserved, $ch) === FALSE) {
				$s = substr_replace($s, $ch, $match[0][1] - 1, 3);
			}
		}
		return $s;
	}

}
