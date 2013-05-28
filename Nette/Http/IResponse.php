<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Http;

use Nette;



/**
 * IHttpResponse interface.
 *
 * @author     David Grudl
 */
interface IResponse
{
	/** @var int cookie expiration: forever (23.1.2037) */
	const PERMANENT = 2116333333;

	/** @var int cookie expiration: until the browser is closed */
	const BROWSER = 0;

	/** HTTP 1.1 response code */
	const S200_OK = 200;
	const S204_NO_CONTENT = 204;
	const S300_MULTIPLE_CHOICES = 300;
	const S301_MOVED_PERMANENTLY = 301;
	const S302_FOUND = 302;
	const S303_SEE_OTHER = 303;
	const S303_POST_GET = 303;
	const S304_NOT_MODIFIED = 304;
	const S307_TEMPORARY_REDIRECT= 307;
	const S400_BAD_REQUEST = 400;
	const S401_UNAUTHORIZED = 401;
	const S403_FORBIDDEN = 403;
	const S404_NOT_FOUND = 404;
	const S405_METHOD_NOT_ALLOWED = 405;
	const S410_GONE = 410;
	const S500_INTERNAL_SERVER_ERROR = 500;
	const S501_NOT_IMPLEMENTED = 501;
	const S503_SERVICE_UNAVAILABLE = 503;

	/**
	 * Sets HTTP response code.
	 * @param  int
	 * @return void
	 */
	function setCode($code);

	/**
	 * Returns HTTP response code.
	 * @return int
	 */
	function getCode();

	/**
	 * Sends a HTTP header and replaces a previous one.
	 * @param  string  header name
	 * @param  string  header value
	 * @return void
	 */
	function setHeader($name, $value);

	/**
	 * Adds HTTP header.
	 * @param  string  header name
	 * @param  string  header value
	 * @return void
	 */
	function addHeader($name, $value);

	/**
	 * Sends a Content-type HTTP header.
	 * @param  string  mime-type
	 * @param  string  charset
	 * @return void
	 */
	function setContentType($type, $charset = NULL);

	/**
	 * Redirects to a new URL.
	 * @param  string  URL
	 * @param  int     HTTP code
	 * @return void
	 */
	function redirect($url, $code = self::S302_FOUND);

	/**
	 * Sets the number of seconds before a page cached on a browser expires.
	 * @param  mixed  timestamp or number of seconds
	 * @return void
	 */
	function setExpiration($seconds);

	/**
	 * Checks if headers have been sent.
	 * @return bool
	 */
	function isSent();

	/**
	 * Returns a list of headers to sent.
	 * @return array
	 */
	function getHeaders();

	/**
	 * Sends a cookie.
	 * @param  string name of the cookie
	 * @param  string value
	 * @param  mixed expiration as unix timestamp or number of seconds; Value 0 means "until the browser is closed"
	 * @param  string
	 * @param  string
	 * @param  bool
	 * @param  bool
	 * @return void
	 */
	function setCookie($name, $value, $expire, $path = NULL, $domain = NULL, $secure = NULL, $httpOnly = NULL);

	/**
	 * Deletes a cookie.
	 * @param  string name of the cookie.
	 * @param  string
	 * @param  string
	 * @param  bool
	 * @return void
	 */
	function deleteCookie($name, $path = NULL, $domain = NULL, $secure = NULL);

}
