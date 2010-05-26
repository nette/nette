<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette\Web
 */

namespace Nette\Web;

use Nette;



/**
 * IHttpRequest provides access scheme for request sent via HTTP.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Web
 */
interface IHttpRequest
{
	/**#@+ HTTP request method */
	const
		GET = 'GET',
		POST = 'POST',
		HEAD = 'HEAD',
		PUT = 'PUT',
		DELETE = 'DELETE';
	/**#@-*/

	/**
	 * Returns URL object.
	 * @return UriScript
	 */
	function getUri();

	/********************* query, post, files & cookies ****************d*g**/

	/**
	 * Returns variable provided to the script via URL query ($_GET).
	 * If no key is passed, returns the entire array.
	 * @param  string key
	 * @param  mixed  default value
	 * @return mixed
	 */
	function getQuery($key = NULL, $default = NULL);

	/**
	 * Returns variable provided to the script via POST method ($_POST).
	 * If no key is passed, returns the entire array.
	 * @param  string key
	 * @param  mixed  default value
	 * @return mixed
	 */
	function getPost($key = NULL, $default = NULL);

	/**
	 * Returns HTTP POST data in raw format (only for "application/x-www-form-urlencoded").
	 * @return string
	 */
	function getPostRaw();

	/**
	 * Returns uploaded file.
	 * @param  string key (or more keys)
	 * @return HttpUploadedFile
	 */
	function getFile($key);

	/**
	 * Returns uploaded files.
	 * @return array
	 */
	function getFiles();

	/**
	 * Returns variable provided to the script via HTTP cookies.
	 * @param  string key
	 * @param  mixed  default value
	 * @return mixed
	 */
	function getCookie($key, $default = NULL);

	/**
	 * Returns variables provided to the script via HTTP cookies.
	 * @return array
	 */
	function getCookies();

	/********************* method & headers ****************d*g**/

	/**
	 * Returns HTTP request method (GET, POST, HEAD, PUT, ...). The method is case-sensitive.
	 * @return string
	 */
	function getMethod();

	/**
	 * Checks HTTP request method.
	 * @param  string
	 * @return bool
	 */
	function isMethod($method);

	/**
	 * Return the value of the HTTP header. Pass the header name as the
	 * plain, HTTP-specified header name (e.g. 'Accept-Encoding').
	 * @param  string
	 * @param  mixed
	 * @return mixed
	 */
	function getHeader($header, $default = NULL);

	/**
	 * Returns all HTTP headers.
	 * @return array
	 */
	function getHeaders();

	/**
	 * Is the request is sent via secure channel (https).
	 * @return bool
	 */
	function isSecured();

	/**
	 * Is AJAX request?
	 * @return bool
	 */
	function isAjax();

	/**
	 * Returns the IP address of the remote client.
	 * @return string
	 */
	function getRemoteAddress();

	/**
	 * Returns the host of the remote client.
	 * @return string
	 */
	function getRemoteHost();

}
