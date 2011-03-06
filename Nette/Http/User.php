<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Web;

use Nette,
	Nette\Environment,
	Nette\Security\IAuthenticator,
	Nette\Security\IAuthorizator;



/**
 * User authentication and authorization.
 *
 * @author     David Grudl
 *
 * @property-read Nette\Security\IIdentity $identity
 * @property   Nette\Security\IAuthenticator $authenticationHandler
 * @property   Nette\Security\IAuthorizator $authorizationHandler
 * @property-read int $logoutReason
 * @property-read array $roles
 * @property-read bool $authenticated
 */
class User extends Nette\Object implements IUser
{
	/** log-out reason {@link User::getLogoutReason()} */
	const MANUAL = IUserStorage::MANUAL,
		INACTIVITY = IUserStorage::INACTIVITY,
		BROWSER_CLOSED = IUserStorage::BROWSER_CLOSED;

	/** @var string  default role for unauthenticated user */
	public $guestRole = 'guest';

	/** @var string  default role for authenticated user without own identity */
	public $authenticatedRole = 'authenticated';

	/** @var array of function(User $sender); Occurs when the user is successfully logged in */
	public $onLoggedIn;

	/** @var array of function(User $sender); Occurs when the user is logged out */
	public $onLoggedOut;

	/** @var Nette\Security\IAuthenticator */
	private $authenticationHandler;

	/** @var Nette\Security\IAuthorizator */
	private $authorizationHandler;

	/** @var UserStorage Session storage for current user */
	private $storage;



	/********************* Authentication ****************d*g**/



	/**
	 * Conducts the authentication process. Parameters are optional.
	 * @param  mixed optional parameter (e.g. username)
	 * @param  mixed optional parameter (e.g. password)
	 * @return void
	 * @throws Nette\Security\AuthenticationException if authentication was not successful
	 */
	public function login($username = NULL, $password = NULL)
	{
		$handler = $this->getAuthenticationHandler();
		if ($handler === NULL) {
			throw new \InvalidStateException('Authentication handler has not been set.');
		}

		$this->logout(TRUE);

		$credentials = func_get_args();
		$this->getStorage()->login($handler->authenticate($credentials));
		$this->onLoggedIn($this);
	}



	/**
	 * Logs out the user from the current session.
	 * @param  bool  clear the identity from persistent storage?
	 * @return void
	 */
	final public function logout($clearIdentity = FALSE)
	{
		$loggedIn = $this->isLoggedIn();
		$this->getStorage()->logout($clearIdentity);
		if ($loggedIn) {
			$this->onLoggedOut($this);
		}
	}



	/**
	 * Is this user authenticated?
	 * @return bool
	 */
	final public function isLoggedIn()
	{
		return $this->getStorage()->isLoggedIn();
	}



	/**
	 * Returns current user identity, if any.
	 * @return Nette\Security\IIdentity
	 */
	final public function getIdentity()
	{
		return $this->getStorage()->getIdentity();
	}



	/**
	 * Returns current user ID, if any.
	 * @return mixed
	 */
	public function getId()
	{
		$identity = $this->getIdentity();
		return $identity ? $identity->getId() : NULL;
	}



	/**
	 * Sets authentication handler.
	 * @param  Nette\Security\IAuthenticator
	 * @return User  provides a fluent interface
	 */
	public function setAuthenticationHandler(IAuthenticator $handler)
	{
		$this->authenticationHandler = $handler;
		return $this;
	}



	/**
	 * Returns authentication handler.
	 * @return Nette\Security\IAuthenticator
	 */
	final public function getAuthenticationHandler()
	{
		if ($this->authenticationHandler === NULL) {
			$this->authenticationHandler = Environment::getService('Nette\\Security\\IAuthenticator');
		}
		return $this->authenticationHandler;
	}



	/**
	 * Changes namespace; allows more users to share a session.
	 * @param  string
	 * @return User  provides a fluent interface
	 */
	public function setNamespace($namespace)
	{
		$this->getStorage()->setNamespace($namespace);
		return $this;
	}



	/**
	 * Returns current namespace.
	 * @return string
	 */
	final public function getNamespace()
	{
		return $this->getStorage()->getNamespace();
	}



	/**
	 * Enables log out after inactivity.
	 * @param  string|int|DateTime number of seconds or timestamp
	 * @param  bool  log out when the browser is closed?
	 * @param  bool  clear the identity from persistent storage?
	 * @return User  provides a fluent interface
	 */
	public function setExpiration($time, $whenBrowserIsClosed = TRUE, $clearIdentity = FALSE)
	{
		$this->getStorage()->setExpiration($time, $whenBrowserIsClosed, $clearIdentity);
		return $this;
	}



	/**
	 * Why was user logged out?
	 * @return int
	 */
	final public function getLogoutReason()
	{
		return $this->getStorage()->getLogoutReason();
	}



	/**
	 * Returns user session storage.
	 * @return Nette\Web\UserStorage
	 */
	protected function getStorage()
	{
		if (!$this->storage) {
			$this->storage = new UserStorage(Environment::getSession());
		}
		return $this->storage;
	}


	
	/********************* Authorization ****************d*g**/



	/**
	 * Returns a list of effective roles that a user has been granted.
	 * @return array
	 */
	public function getRoles()
	{
		if (!$this->isLoggedIn()) {
			return array($this->guestRole);
		}

		$identity = $this->getIdentity();
		return $identity ? $identity->getRoles() : array($this->authenticatedRole);
	}



	/**
	 * Is a user in the specified effective role?
	 * @param  string
	 * @return bool
	 */
	final public function isInRole($role)
	{
		return in_array($role, $this->getRoles(), TRUE);
	}



	/**
	 * Has a user effective access to the Resource?
	 * If $resource is NULL, then the query applies to all resources.
	 * @param  string  resource
	 * @param  string  privilege
	 * @return bool
	 */
	public function isAllowed($resource = IAuthorizator::ALL, $privilege = IAuthorizator::ALL)
	{
		$handler = $this->getAuthorizationHandler();
		if (!$handler) {
			throw new \InvalidStateException("Authorization handler has not been set.");
		}

		foreach ($this->getRoles() as $role) {
			if ($handler->isAllowed($role, $resource, $privilege)) return TRUE;
		}

		return FALSE;
	}



	/**
	 * Sets authorization handler.
	 * @param  Nette\Security\IAuthorizator
	 * @return User  provides a fluent interface
	 */
	public function setAuthorizationHandler(IAuthorizator $handler)
	{
		$this->authorizationHandler = $handler;
		return $this;
	}



	/**
	 * Returns current authorization handler.
	 * @return Nette\Security\IAuthorizator
	 */
	final public function getAuthorizationHandler()
	{
		if ($this->authorizationHandler === NULL) {
			$this->authorizationHandler = Environment::getService('Nette\\Security\\IAuthorizator');
		}
		return $this->authorizationHandler;
	}


}
