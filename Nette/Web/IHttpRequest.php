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



/**
 * IHttpRequest provides access scheme for request sent via HTTP.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Web
 * @version    $Revision$ $Date$
 */
interface IHttpRequest
{
	/**
	 * Returns HTTP request method (GET, POST, HEAD, PUT, ...).
	 * @return string
	 */
	function getMethod();

	/**
	 * Returns the full URL.
	 * @return Uri
	 */
	function getUri();

	/**
	 * Returns all variables provided to the script via URL query ($_GET).
	 * @return array
	 */
	function getQuery();

	/**
	 * Returns all variables provided to the script via POST method ($_POST).
	 * @return array
	 */
	function getPost();

	/**
	 * Returns all uploaded files.
	 * @return array
	 */
	function getFiles();

	/**
	 * Returns all HTTP cookies.
	 * @return array
	 */
	function getCookies();

	/**
	 * Returns all HTTP headers
	 * @return array
	 */
	function getHeaders();

	/**
	 * Is the request is sent via secure channel (https).
	 * @return boolean
	 */
	function isSecured();

	/**
	 * Is Ajax request?
	 * @return boolean
	 */
	function isAjax();
}
