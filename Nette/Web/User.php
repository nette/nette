<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Web
 */

/*namespace Nette\Web;*/

/*use Nette\Environment;*/



require_once dirname(__FILE__) . '/../Object.php';

require_once dirname(__FILE__) . '/../Web/IUser.php';



/**
 * Authentication and authorization.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Web
 *
 * @property-read Nette\Security\IIdentity $identity
 * @property   Nette\Security\IAuthenticator $authenticationHandler
 * @property   Nette\Security\IAuthorizator $authorizationHandler
 * @property-read int $signOutReason
 * @property-read array $roles
 * @property-read bool $authenticated
 */
class User extends /*Nette\*/Object implements IUser
{
	/**#@+ sign-out reason {@link User::getSignOutReason()} */
	const MANUAL = 1;
	const INACTIVITY = 2;
	const BROWSER_CLOSED = 3;
	/**#@-*/

	/** @var string  default role for unauthenticated user */
	public $guestRole = 'guest';

	/** @var string  default role for authenticated user without own identity */
	public $authenticatedRole = 'authenticated';

	/** @var array of function(User $sender); Occurs when the user is successfully authenticated */
	public $onAuthenticated;

	/** @var array of function(User $sender); Occurs when the user is logged off */
	public $onSignedOut;

	/** @var Nette\Security\IAuthenticator */
	private $authenticationHandler;

	/** @var Nette\Security\IAuthorizator */
	private $authorizationHandler;

	/** @var string */
	private $namespace = '';

	/** @var SessionNamespace */
	private $session;



	/********************* Authentication ****************d*g**/



	/**
	 * Conducts the authentication process.
	 * @param  string
	 * @param  string
	 * @param  mixed
	 * @return void
	 * @throws Nette\Security\AuthenticationException if authentication was not successful
	 */
	public function authenticate($username, $password, $extra = NULL)
	{
		$handler = $this->getAuthenticationHandler();
		if ($handler === NULL) {
			throw new /*\*/InvalidStateException('Authentication handler has not been set.');
		}

		$this->signOut(TRUE);

		$credentials = array(
			/*Nette\Security\*/IAuthenticator::USERNAME => $username,
			/*Nette\Security\*/IAuthenticator::PASSWORD => $password,
			'extra' => $extra,
		);

		$this->setIdentity($handler->authenticate($credentials));
		$this->setAuthenticated(TRUE);
		$this->onAuthenticated($this);
	}



	/**
	 * Logs off the user from the current session.
	 * @param  bool  clear the identity from persistent storage?
	 * @return void
	 */
	final public function signOut($clearIdentity = FALSE)
	{
		if ($this->isAuthenticated()) {
			$this->setAuthenticated(FALSE);
			$this->onSignedOut($this);
		}

		if ($clearIdentity) {
			$this->setIdentity(NULL);
		}
	}



	/**
	 * Is this user authenticated?
	 * @return bool
	 */
	final public function isAuthenticated()
	{
		$session = $this->getSessionNamespace(FALSE);
		return $session && $session->authenticated;
	}



	/**
	 * Returns current user identity, if any.
	 * @return Nette\Security\IIdentity
	 */
	final public function getIdentity()
	{
		$session = $this->getSessionNamespace(FALSE);
		return $session ? $session->identity : NULL;
	}



	/**
	 * Sets authentication handler.
	 * @param  Nette\Security\IAuthenticator
	 * @return User  provides a fluent interface
	 */
	public function setAuthenticationHandler(/*Nette\Security\*/IAuthenticator $handler)
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
			$this->authenticationHandler = Environment::getService('Nette\Security\IAuthenticator');
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
		if ($this->namespace !== $namespace) {
			$this->namespace = (string) $namespace;
			$this->session = NULL;
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
	 * Enables sign out after inactivity.
	 * @param  string|int|DateTime number of seconds or timestamp
	 * @param  bool  sign out when the browser is closed?
	 * @param  bool  clear the identity from persistent storage?
	 * @return User  provides a fluent interface
	 */
	public function setExpiration($time, $whenBrowserIsClosed = TRUE, $clearIdentity = FALSE)
	{
		$session = $this->getSessionNamespace(TRUE);
		if ($time) {
			$time = /*Nette\*/Tools::createDateTime($time)->format('U');
			$session->expireTime = $time;
			$session->expireDelta = $time - time();

		} else {
			unset($session->expireTime, $session->expireDelta);
		}

		$session->expireIdentity = (bool) $clearIdentity;
		$session->expireBrowser = (bool) $whenBrowserIsClosed;
		$session->browserCheck = TRUE;
		$session->setExpiration(0, 'browserCheck');
		return $this;
	}



	/**
	 * Why was user signed out?
	 * @return int
	 */
	final public function getSignOutReason()
	{
		$session = $this->getSessionNamespace(FALSE);
		return $session ? $session->reason : NULL;
	}



	/**
	 * Returns and initializes $this->session.
	 * @return SessionNamespace
	 */
	protected function getSessionNamespace($need)
	{
		if ($this->session !== NULL) {
			return $this->session;
		}

		$sessionHandler = $this->getSession();
		if (!$need && !$sessionHandler->exists()) {
			return NULL;
		}

		$this->session = $session = $sessionHandler->getNamespace('Nette.Web.User/' . $this->namespace);

		if (!($session->identity instanceof /*Nette\Security\*/IIdentity) || !is_bool($session->authenticated)) {
			$session->remove();
		}

		if ($session->authenticated && $session->expireBrowser && !$session->browserCheck) { // check if browser was closed?
			$session->reason = self::BROWSER_CLOSED;
			$session->authenticated = FALSE;
			$this->onSignedOut($this);
			if ($session->expireIdentity) {
				unset($session->identity);
			}
		}

		if ($session->authenticated && $session->expireDelta > 0) { // check time expiration
			if ($session->expireTime < time()) {
				$session->reason = self::INACTIVITY;
				$session->authenticated = FALSE;
				$this->onSignedOut($this);
				if ($session->expireIdentity) {
					unset($session->identity);
				}
			}
			$session->expireTime = time() + $session->expireDelta; // sliding expiration
		}

		if (!$session->authenticated) {
			unset($session->expireTime, $session->expireDelta, $session->expireIdentity,
				$session->expireBrowser, $session->browserCheck, $session->authTime);
		}

		return $this->session;
	}



	/**
	 * Sets the authenticated status of this user.
	 * @param  bool  flag indicating the authenticated status of user
	 * @return User  provides a fluent interface
	 */
	protected function setAuthenticated($state)
	{
		$session = $this->getSessionNamespace(TRUE);
		$session->authenticated = (bool) $state;

		// Session Fixation defence
		$this->getSession()->regenerateId();

		if ($state) {
			$session->reason = NULL;
			$session->authTime = time(); // informative value

		} else {
			$session->reason = self::MANUAL;
			$session->authTime = NULL;
		}
		return $this;
	}



	/**
	 * Sets the user identity.
	 * @param  IIdentity
	 * @return User  provides a fluent interface
	 */
	protected function setIdentity(/*Nette\Security\*/IIdentity $identity = NULL)
	{
		$this->getSessionNamespace(TRUE)->identity = $identity;
		return $this;
	}



	/********************* Authorization ****************d*g**/



	/**
	 * Returns a list of effective roles that a user has been granted.
	 * @return array
	 */
	public function getRoles()
	{
		if (!$this->isAuthenticated()) {
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
	public function isAllowed($resource = NULL, $privilege = NULL)
	{
		$handler = $this->getAuthorizationHandler();
		if (!$handler) {
			throw new /*\*/InvalidStateException("Authorization handler has not been set.");
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
	public function setAuthorizationHandler(/*Nette\Security\*/IAuthorizator $handler)
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
			$this->authorizationHandler = Environment::getService('Nette\Security\IAuthorizator');
		}
		return $this->authorizationHandler;
	}



	/********************* backend ****************d*g**/



	/**
	 * Returns session handler.
	 * @return Nette\Web\Session
	 */
	protected function getSession()
	{
		return Environment::getSession();
	}

}
