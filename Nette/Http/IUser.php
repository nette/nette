<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Http;

use Nette;



/**
 * User authentication and authorization.
 *
 * @author     David Grudl
 */
interface IUser
{

	/**
	 * Conducts the authentication process.
	 * @param  mixed optional parameter (e.g. username)
	 * @param  mixed optional parameter (e.g. password)
	 * @return void
	 * @throws Nette\Security\AuthenticationException if authentication was not successful
	 */
	function login();

	/**
	 * Logs out the user from the current session.
	 * @return void
	 */
	function logout($clearIdentity = FALSE);

	/**
	 * Is this user authenticated?
	 * @return bool
	 */
	function isLoggedIn();

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
	function setAuthenticationHandler(Nette\Security\IAuthenticator $handler);

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
	function setAuthorizationHandler(Nette\Security\IAuthorizator $handler);

	/**
	 * Returns current authorization handler.
	 * @return Nette\Security\IAuthorizator
	 */
	function getAuthorizationHandler();

}
