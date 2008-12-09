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
 * @package    Nette\Web
 * @version    $Id$
 */

/*namespace Nette\Web;*/



/**
 * Authentication and authorization.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette\Web
 */
interface IUser
{

	/**
	 * Conducts the authentication process.
	 * @param  string
	 * @param  string
	 * @param  mixed
	 * @return void
	 * @throws Nette\Security\AuthenticationException if authentication was not successful
	 */
	function authenticate($username, $password, $extra = NULL);

	/**
	 * Logs off the user from the current session.
	 * @return void
	 */
	function signOut($clearIdentity = FALSE);

	/**
	 * Is this user authenticated?
	 * @return bool
	 */
	function isAuthenticated();

	/**
	 * Returns current user identity, if any.
	 * @return Nette\Security\IIdentity
	 */
	function getIdentity();

	/**
	 * Sets authentication handler.
	 * @param  Nette\Security\IAuthenticator
	 * @return void
	 */
	function setAuthenticationHandler(/*Nette\Security\*/IAuthenticator $handler);

	/**
	 * Returns authentication handler.
	 * @return Nette\Security\IAuthenticator
	 */
	function getAuthenticationHandler();

	/**
	 * Changes namespace; allows more users to share a session.
	 * @param  string
	 * @return void
	 */
	function setNamespace($namespace);

	/**
	 * Returns current namespace.
	 * @return string
	 */
	function getNamespace();

	/**
	 * Returns a list of roles that a user has been granted.
	 * @return array
	 */
	function getRoles();

	/**
	 * Is a user in the specified role?
	 * @param  string
	 * @return bool
	 */
	function isInRole($role);

	/**
	 * Has a user access to the Resource?
	 * @return bool
	 */
	function isAllowed();

	/**
	 * Sets authorization handler.
	 * @param  Nette\Security\IAuthorizator
	 * @return void
	 */
	function setAuthorizationHandler(/*Nette\Security\*/IAuthorizator $handler);

	/**
	 * Returns current authorization handler.
	 * @return Nette\Security\IAuthorizator
	 */
	function getAuthorizationHandler();

}
