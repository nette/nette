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

/*namespace Nette\Web;*/



/**
 * User authentication and authorization.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
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
	function login($username, $password, $extra = NULL);

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
