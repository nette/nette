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
 * IHttpResponse interface.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Web
 * @version    $Revision$ $Date$
 */
interface IHttpResponse
{
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

    function setHeader($header, $replace = FALSE);

    function getHeader($header);

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
