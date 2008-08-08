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



/**
 * URI Syntax (RFC 3986).
 *
 * http://user:pass@nettephp.com:8042/en/manual.html?name=param#fragment
 * \__/^^^\_________________________/\_____________/^\________/^\______/
 *   |                |                     |            |         |
 * scheme         authority               path         query    fragment
 *
 * authority:   [user[:pass]@]host[:port]
 * hostUri:     http://user:pass@nettephp.com:8042
 *
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Web
 */
class Uri extends /*Nette::*/Object
{
	/** @var array */
	static public $defaultPorts = array(
		'http' => 80,
		'https' => 443,
		'ftp' => 21,
		'news' => 119,
		'nntp' => 119,
	);

	/** @var string */
	public $scheme = '';

	/** @var string */
	public $user = '';

	/** @var string */
	public $pass = '';

	/** @var string */
	public $host = '';

	/** @var int */
	public $port = NULL;

	/** @var string */
	public $path = '';

	/** @var string */
	public $query = '';

	/** @var string */
	public $fragment = '';



	/**
	 * @param  string  URL
	 * @throws InvalidArgumentException
	 */
	public function __construct($uri = NULL)
	{
		if ($uri !== NULL) {
			$parts = @parse_url($uri); // intentionally @
			if ($parts === FALSE) {
				throw new /*::*/InvalidArgumentException('Malformed or unsupported URI.');
			}

			foreach ($parts as $key => $val) {
				$this->$key = $val;
			}

			if (!$this->port && isset(self::$defaultPorts[$this->scheme])) {
				$this->port = self::$defaultPorts[$this->scheme];
			}
		}
	}



	/**
	 * Returns the entire URI including query string and fragment.
	 * @return string
	 */
	public function getAbsoluteUri()
	{
		return $this->scheme . '://' . $this->getAuthority() . $this->path
			. ($this->query == '' ? '' : '?' . $this->query)
			. ($this->fragment == '' ? '' : '#' . $this->fragment);
	}



	/**
	 * Returns the [user[:pass]@]host[:port] part of URI.
	 * @return string
	 */
	public function getAuthority()
	{
		$authority = $this->host;
		if ($this->port && isset(self::$defaultPorts[$this->scheme]) && $this->port !== self::$defaultPorts[$this->scheme]) {
			$authority .= ':' . $this->port;
		}

		if ($this->user != '' && $this->scheme !== 'http' && $this->scheme !== 'https') {
			$authority = $this->user . ($this->pass == '' ? '' : ':' . $this->pass) . '@' . $authority;
		}

		return $authority;
	}



	/**
	 * Returns the scheme and authority part of URI.
	 * @return string
	 */
	public function getHostUri()
	{
		return $this->scheme . '://' . $this->getAuthority();
	}



	/**
	 * URI comparsion (this object must be in canonical form).
	 * @param  string
	 * @return bool
	 */
	public function isEqual($uri)
	{
		// compare host + path
		$part = self::unescape(strtok($uri, '?#'), '%/');
		if (strncmp($part, '//', 2) === 0) { // absolute URI without scheme
			if ($part !== '//' . $this->getAuthority() . $this->path) return FALSE;

		} elseif (strncmp($part, '/', 1) === 0) { // absolute path
			if ($part !== $this->path) return FALSE;

		} else {
			if ($part !== $this->scheme . '://' . $this->getAuthority() . $this->path) return FALSE;
		}

		// compare query strings
		$part = strtok('?#');
		if ($part !== FALSE) {
			$tmp = explode('&', self::unescape($part, '%&'));
			sort($tmp);
			if (implode('&', $tmp) !== $this->query) {
				return FALSE;
			}
		}

		return TRUE;
	}



	/**
	 * Transform to canonical form.
	 * @return void
	 */
	public function canonicalize()
	{
		$this->path = $this->path == '' ? '/' : self::unescape($this->path, '%/');

		$this->host = strtolower(rawurldecode($this->host));

		if ($this->query !== '') {
			$tmp = explode('&', self::unescape($this->query, '%&'));
			sort($tmp);
			$this->query = implode('&', $tmp);
		}
	}



	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->getAbsoluteUri();
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
		$offset = 0;
		$max = strlen($s) - 3;
		$res = '';
		do {
			$pos = strpos($s, '%', $offset);
			if ($pos === FALSE || $pos > $max) {
				return $res . substr($s, $offset);
			}
			$ch = chr(hexdec($s[$pos + 1] . $s[$pos + 2]));
			if (strpos($reserved, $ch) === FALSE) {
				$res .= substr($s, $offset, $pos - $offset) . $ch;
			} else {
				$res .= substr($s, $offset, $pos - $offset + 3);
			}
			$offset = $pos + 3;
		} while (TRUE);
	}

}
