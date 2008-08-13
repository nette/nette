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
 * Authentication and authorization.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Web
 */
interface IUser
{
	/**
	 * @return Nette::Security::IAuthenticator
	 */
	function getAuthenticationHandler();

	/**
	 * @return Nette::Security::IAuthorizator
	 */
	function getAuthorizationHandler();

	/**
	 * Check the authenticated status.
	 * @return void
	 */
	function authenticate();

	/**
	 * Removes the authentication flag from persistent storage.
	 * @return void
	 */
	function signOut();

	/**
	 * Indicates whether this user is authenticated.
	 * @return bool true, if this user is authenticated, otherwise false.
	 */
	function isAuthenticated();

	/**
	 * @return Nette::Security::IIdentity
	 */
	function getIdentity();

	/**
	 * Returns a role this user has been granted.
	 * @return array
	 */
	function getRoles();

	/**
	 * Returns a role this user has been granted.
	 * @param  string
	 * @return bool
	 */
	function isInRole($role);

	/**
	 * Returns TRUE if and only if the user has access to the resource.
	 * @param  string  resource
	 * @return bool
	 */
	function isAllowed($resource = NULL);

}
