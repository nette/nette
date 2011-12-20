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

use Nette,
	Nette\Security\IAuthenticator,
	Nette\Security\IAuthorizator,
	Nette\Security\IIdentity;



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
	/** log-out reason {@link User::getLogoutReason()} */
	const MANUAL = 1,
		INACTIVITY = 2,
		BROWSER_CLOSED = 3;

	/** @var string  default role for unauthenticated user */
	public $guestRole = 'guest';

	/** @var string  default role for authenticated user without own identity */
	public $authenticatedRole = 'authenticated';

	/** @var array of function(User $sender); Occurs when the user is successfully logged in */
	public $onLoggedIn;

	/** @var array of function(User $sender); Occurs when the user is logged out */
	public $onLoggedOut;

	/** @var string */
	private $namespace = '';

	/** @var Session */
	private $session;

	/** @var Session */
	private $section;

	/** @var Nette\DI\IContainer */
	private $context;



	public function __construct(Session $session, Nette\DI\IContainer $context)
	{
		$this->session = $session;
		$this->context = $context;
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
		$this->setIdentity($this->context->authenticator->authenticate($credentials));
		$this->setAuthenticated(TRUE);
		$this->onLoggedIn($this);
	}



	/**
	 * Logs out the user from the current session.
	 * @param  bool  clear the identity from persistent storage?
	 * @return void
	 */
	final public function logout($clearIdentity = FALSE)
	{
		if ($this->isLoggedIn()) {
			$this->setAuthenticated(FALSE);
			$this->onLoggedOut($this);
		}

		if ($clearIdentity) {
			$this->setIdentity(NULL);
		}
	}



	/**
	 * Is this user authenticated?
	 * @return bool
	 */
	final public function isLoggedIn()
	{
		$section = $this->getSessionSection(FALSE);
		return $section && $section->authenticated;
	}



	/**
	 * Returns current user identity, if any.
	 * @return Nette\Security\IIdentity
	 */
	final public function getIdentity()
	{
		$section = $this->getSessionSection(FALSE);
		return $section ? $section->identity : NULL;
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
		$this->context->removeService('authenticator');
		$this->context->authenticator = $handler;
		return $this;
	}



	/**
	 * Returns authentication handler.
	 * @return Nette\Security\IAuthenticator
	 */
	final public function getAuthenticator()
	{
		return $this->context->authenticator;
	}



	/**
	 * Changes namespace; allows more users to share a session.
	 * @param  string
	 * @return User  provides a fluent interface
	 */
	public function setNamespace($namespace)
	{
		if ($this->namespace !== $namespace) {
			$this->namespace = (string) $namespace;
			$this->section = NULL;
		}
		return $this;
	}



	/**
	 * Returns current namespace.
	 * @return string
	 */
	final public function getNamespace()
	{
		return $this->namespace;
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
		$section = $this->getSessionSection(TRUE);
		if ($time) {
			$time = Nette\DateTime::from($time)->format('U');
			$section->expireTime = $time;
			$section->expireDelta = $time - time();

		} else {
			unset($section->expireTime, $section->expireDelta);
		}

		$section->expireIdentity = (bool) $clearIdentity;
		$section->expireBrowser = (bool) $whenBrowserIsClosed;
		$section->browserCheck = TRUE;
		$section->setExpiration(0, 'browserCheck');
		return $this;
	}



	/**
	 * Why was user logged out?
	 * @return int
	 */
	final public function getLogoutReason()
	{
		$section = $this->getSessionSection(FALSE);
		return $section ? $section->reason : NULL;
	}



	/**
	 * Returns and initializes $this->section.
	 * @return SessionSection
	 */
	protected function getSessionSection($need)
	{
		if ($this->section !== NULL) {
			return $this->section;
		}

		if (!$need && !$this->session->exists()) {
			return NULL;
		}

		$this->section = $section = $this->session->getSection('Nette.Web.User/' . $this->namespace);

		if (!$section->identity instanceof IIdentity || !is_bool($section->authenticated)) {
			$section->remove();
		}

		if ($section->authenticated && $section->expireBrowser && !$section->browserCheck) { // check if browser was closed?
			$section->reason = self::BROWSER_CLOSED;
			$section->authenticated = FALSE;
			$this->onLoggedOut($this);
			if ($section->expireIdentity) {
				unset($section->identity);
			}
		}

		if ($section->authenticated && $section->expireDelta > 0) { // check time expiration
			if ($section->expireTime < time()) {
				$section->reason = self::INACTIVITY;
				$section->authenticated = FALSE;
				$this->onLoggedOut($this);
				if ($section->expireIdentity) {
					unset($section->identity);
				}
			}
			$section->expireTime = time() + $section->expireDelta; // sliding expiration
		}

		if (!$section->authenticated) {
			unset($section->expireTime, $section->expireDelta, $section->expireIdentity,
				$section->expireBrowser, $section->browserCheck, $section->authTime);
		}

		return $this->section;
	}



	/**
	 * Sets the authenticated status of this user.
	 * @param  bool  flag indicating the authenticated status of user
	 * @return User  provides a fluent interface
	 */
	protected function setAuthenticated($state)
	{
		$section = $this->getSessionSection(TRUE);
		$section->authenticated = (bool) $state;

		// Session Fixation defence
		$this->session->regenerateId();

		if ($state) {
			$section->reason = NULL;
			$section->authTime = time(); // informative value

		} else {
			$section->reason = self::MANUAL;
			$section->authTime = NULL;
		}
		return $this;
	}



	/**
	 * Sets the user identity.
	 * @param  Nette\Security\IIdentity
	 * @return User  provides a fluent interface
	 */
	protected function setIdentity(IIdentity $identity = NULL)
	{
		$this->getSessionSection(TRUE)->identity = $identity;
		return $this;
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
		$authorizator = $this->context->authorizator;
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
		$this->context->removeService('authorizator');
		$this->context->authorizator = $handler;
		return $this;
	}



	/**
	 * Returns current authorization handler.
	 * @return Nette\Security\IAuthorizator
	 */
	final public function getAuthorizator()
	{
		return $this->context->authorizator;
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
