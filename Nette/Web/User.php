<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2009 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Web
 * @version    $Id$
 */

/*namespace Nette\Web;*/

/*use Nette\Environment;*/



require_once dirname(__FILE__) . '/../Object.php';

require_once dirname(__FILE__) . '/../Web/IUser.php';



/**
 * Authentication and authorization.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @package    Nette\Web
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

	/** @var array of event handlers; Occurs when the user is successfully authenticated; function(User $sender) */
	public $onAuthenticated;

	/** @var array of event handlers; Occurs when the user is logged off; function(User $sender) */
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
	 * @return void
	 */
	public function setAuthenticationHandler(/*Nette\Security\*/IAuthenticator $handler)
	{
		$this->authenticationHandler = $handler;
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
	 * @return void
	 */
	public function setNamespace($namespace)
	{
		if ($this->namespace !== $namespace) {
			$this->namespace = (string) $namespace;
			$this->session = NULL;
		}
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
	 * @param  int   number of seconds or timestamp
	 * @param  bool  sign out when the browser is closed?
	 * @param  bool  clear the identity from persistent storage?
	 * @return void
	 */
	public function setExpiration($seconds, $whenBrowserIsClosed = TRUE, $clearIdentity = FALSE)
	{
		$session = $this->getSessionNamespace(TRUE);
		if ($seconds > 0) {
			if ($seconds <= /*Nette\*/Tools::YEAR) {
				$seconds += time();
			}
			$session->expireTime = $seconds;
			$session->expireDelta = $seconds - time();

		} else {
			unset($session->expireTime, $session->expireDelta);
		}

		$session->expireIdentity = (bool) $clearIdentity;
		$session->expireBrowser = (bool) $whenBrowserIsClosed;
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

		if (!($session->identity instanceof /*Nette\Security\*/IIdentity)) {
			$session->remove();
		}

		if (!is_bool($session->authenticated)) {
			$session->remove();
		}

		if ($session->authenticated && $session->expireBrowser) { // check if browser was closed?
			if ($session->authKey !== $this->getHttpRequest()->getCookie('nette-authkey')) {
				$session->reason = self::BROWSER_CLOSED;
				$session->authenticated = FALSE;
				unset($session->authKey);
				$this->onSignedOut($this);
				if ($session->expireIdentity) {
					unset($session->identity);
				}
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

		return $this->session;
	}



	/**
	 * Set the authenticated status of this user.
	 * @param  bool  flag indicating the authenticated status of user
	 * @return void
	 */
	protected function setAuthenticated($state)
	{
		$session = $this->getSessionNamespace(TRUE);
		$session->authenticated = (bool) $state;

		// Session Fixation defence
		$this->getSession()->regenerateId();

		if ($state) {
			$session->reason = NULL;
			$session->expireBrowser = TRUE;
			$session->authTime = time(); // informative value
			$session->authKey = $this->getHttpRequest()->getCookie('nette-authkey');

			if (!$session->authKey) {
				$session->authKey = (string) lcg_value();

				$params = $this->getSession()->getCookieParams();
				$this->getHttpResponse()->setCookie(
					'nette-authkey',
					$session->authKey,
					HttpResponse::BROWSER,
					$params['path'],
					$params['domain'],
					$params['secure']
				);
			}
		} else {
			$session->reason = self::MANUAL;
			unset($session->authKey, $session->expireTime, $session->expireDelta,
			$session->expireIdentity, $session->expireBrowser, $session->authTime);
		}
	}



	protected function setIdentity(/*Nette\Security\*/IIdentity $identity = NULL)
	{
		$this->getSessionNamespace(TRUE)->identity = $identity;
	}



	/********************* Authorization ****************d*g**/



	/**
	 * Returns a list of roles that a user has been granted.
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
	 * Is a user in the specified role?
	 * @param  string
	 * @return bool
	 */
	final public function isInRole($role)
	{
		return in_array($role, $this->getRoles(), TRUE);
	}



	/**
	 * Has a user access to the Resource?
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
	 * @return void
	 */
	public function setAuthorizationHandler(/*Nette\Security\*/IAuthorizator $handler)
	{
		$this->authorizationHandler = $handler;
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



	/**
	 * @return Nette\Web\IHttpRequest
	 */
	protected function getHttpRequest()
	{
		return Environment::getHttpRequest();
	}



	/**
	 * @return Nette\Web\IHttpResponse
	 */
	protected function getHttpResponse()
	{
		return Environment::getHttpResponse();
	}

}
