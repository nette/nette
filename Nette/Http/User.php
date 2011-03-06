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

use Nette,
	Nette\Security\IAuthenticator,
	Nette\Security\IAuthorizator;



/**
 * User authentication and authorization.
 *
 * @author     David Grudl
 *
 * @property-read bool $loggedIn
 * @property-read Nette\Security\IIdentity $identity
 * @property-read mixed $id
 * @property   Nette\Security\IAuthenticator $authenticator
 * @property   string $namespace
 * @property-read int $logoutReason
 * @property-read array $roles
 * @property   Nette\Security\IAuthorizator $authorizator
 */
class User extends Nette\Object implements IUser
{
	/** @deprecated */
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

	/** @var UserStorage Session storage for current user */
	private $storage;

	/** @var Nette\Security\IAuthenticator */
	private $authenticator;

	/** @var Nette\Security\IAuthorizator */
	private $authorizator;

	/** @var Nette\DI\IContainer */
	private $context;



	public function __construct(UserStorage $storage, Nette\DI\IContainer $context)
	{
		$this->storage = $storage;
		$this->context = $context; // with Nette\Security\IAuthenticator, Nette\Security\IAuthorizator
	}



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
		$this->logout(TRUE);
		$credentials = func_get_args();
		$this->storage->login($this->getAuthenticator()->authenticate($credentials));
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
		$this->storage->logout($clearIdentity);
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
		return $this->storage->isLoggedIn();
	}



	/**
	 * Returns current user identity, if any.
	 * @return Nette\Security\IIdentity
	 */
	final public function getIdentity()
	{
		return $this->storage->getIdentity();
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
	public function setAuthenticator(IAuthenticator $handler)
	{
		$this->authenticator = $handler;
		return $this;
	}



	/**
	 * Returns authentication handler.
	 * @return Nette\Security\IAuthenticator
	 */
	final public function getAuthenticator()
	{
		return $this->authenticator ?: $this->context->getByType('Nette\Security\IAuthenticator');
	}



	/**
	 * Changes namespace; allows more users to share a session.
	 * @param  string
	 * @return User  provides a fluent interface
	 */
	public function setNamespace($namespace)
	{
		$this->storage->setNamespace($namespace);
		return $this;
	}



	/**
	 * Returns current namespace.
	 * @return string
	 */
	final public function getNamespace()
	{
		return $this->storage->getNamespace();
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
		$this->storage->setExpiration($time, $whenBrowserIsClosed, $clearIdentity);
		return $this;
	}



	/**
	 * Why was user logged out?
	 * @return int
	 */
	final public function getLogoutReason()
	{
		return $this->storage->getLogoutReason();
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
		return $identity && $identity->getRoles() ? $identity->getRoles() : array($this->authenticatedRole);
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
		$authorizator = $this->getAuthorizator();
		foreach ($this->getRoles() as $role) {
			if ($authorizator->isAllowed($role, $resource, $privilege)) {
				return TRUE;
			}
		}

		return FALSE;
	}



	/**
	 * Sets authorization handler.
	 * @param  Nette\Security\IAuthorizator
	 * @return User  provides a fluent interface
	 */
	public function setAuthorizator(IAuthorizator $handler)
	{
		$this->authorizator = $handler;
		return $this;
	}



	/**
	 * Returns current authorization handler.
	 * @return Nette\Security\IAuthorizator
	 */
	final public function getAuthorizator()
	{
		return $this->authorizator ?: $this->context->getByType('Nette\Security\IAuthorizator');
	}



	/********************* deprecated ****************d*g**/

	/** @deprecated */
	function setAuthenticationHandler($v)
	{
		trigger_error(__METHOD__ . '() is deprecated; use setAuthenticator() instead.', E_USER_WARNING);
		return $this->setAuthenticator($v);
	}

	/** @deprecated */
	function setAuthorizationHandler($v)
	{
		trigger_error(__METHOD__ . '() is deprecated; use setAuthorizator() instead.', E_USER_WARNING);
		return $this->setAuthorizator($v);
	}

}
