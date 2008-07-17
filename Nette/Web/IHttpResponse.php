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


/**
 * IHttpResponse interface.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Web
 */
interface IHttpResponse
{
	// HTTP 1.1 response codes
	const
		S200_OK = 200,
		S204_NO_CONTENT = 204,
		S300_MULTIPLE_CHOICES = 300,
		S301_MOVED_PERMANENTLY = 301,
		S302_FOUND = 302,
		S303_SEE_OTHER = 303,
		S303_POST_GET = 303,
		S304_NOT_MODIFIED = 304,
		S307_TEMPORARY_REDIRECT= 307,
		S400_BAD_REQUEST = 400,
		S401_UNAUTHORIZED = 401,
		S403_FORBIDDEN = 403,
		S404_NOT_FOUND = 404,
		S410_GONE = 410,
		S500_INTERNAL_SERVER_ERROR = 500,
		S501_NOT_IMPLEMENTED = 501,
		S503_SERVICE_UNAVAILABLE = 503;

	/** @var int  limit whether expiration is number of seconds starting from current time or timestamp */
	const EXPIRATION_DELTA_LIMIT = 31622400; // 366 days

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
	 * Send a raw HTTP header.
	 * @param  string  header
	 * @param  bool    replace?
	 * @return void
	 */
	function setHeader($header, $replace = FALSE);

	/**
	 * Returns a list of headers to sent.
	 * @return array
	 */
	function getHeaders();

	/**
	 * Defines a new cookie.
	 * @param  string name of the cookie.
	 * @param  string value
	 * @param  int expiration as unix timestamp
	 * @param  string
	 * @param  string
	 * @param  bool
	 * @return void
	 */
	function setCookie($name, $value, $expire, $path = NULL, $domain = NULL, $secure = NULL);

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
