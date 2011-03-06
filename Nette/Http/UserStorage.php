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
	Nette\Security\IIdentity;


/**
 * Session storage for user object.
 *
 * @author David Grudl, Jan Tichý
 */
class UserStorage extends Nette\Object implements IUserStorage
{
	/** @var string */
	private $namespace = '';

	/** @var Session */
	private $sessionHandler;

	/** @var SessionNamespace */
	private $sessionNamespace;

	/**
	 * Inject session handler. No more Environment.
	 * @param Nette\Web\Session
	 */
	public function  __construct(Session $sessionHandler)
	{
		$this->sessionHandler = $sessionHandler;
	}

	/**
	 * Logs in the user to the current session.
	 * @param IIdentity
	 * @return UserStorage Provides a fluent interface
	 */
	public function login(IIdentity $identity)
	{
		$this->setIdentity($identity);
		$this->setAuthenticated(TRUE);
		return $this;
	}

	/**
	 * Logs out the user from the current session.
	 * @param bool Clear the identity from persistent storage?
	 * @return UserStorage Provides a fluent interface
	 */
	public function logout($clearIdentity = FALSE)
	{
		$this->setAuthenticated(FALSE);
		if ($clearIdentity) {
			$this->setIdentity(NULL);
		}
	}
	
	/**
	 * Is this user authenticated?
	 * @return bool
	 */
	public function isLoggedIn()
	{
		$session = $this->getSessionNamespace(FALSE);
		return $session && $session->authenticated;
	}

	/**
	 * Returns current user identity, if any.
	 * @return Nette\Security\IIdentity
	 */
	public function getIdentity()
	{
		$session = $this->getSessionNamespace(FALSE);
		return $session ? $session->identity : NULL;
	}

	/**
	 * Changes namespace; allows more users to share a session.
	 * @param string
	 * @return UserStorage Provides a fluent interface
	 */
	public function setNamespace($namespace)
	{
		if ($this->namespace !== $namespace) {
			$this->namespace = (string) $namespace;
			$this->sessionNamespace = NULL;
		}
		return $this;
	}

	/**
	 * Returns current namespace.
	 * @return string
	 */
	public function getNamespace()
	{
		return $this->namespace;
	}

	/**
	 * Enables log out after inactivity.
	 * @param string|int|DateTime Number of seconds or timestamp
	 * @param bool Log out when the browser is closed?
	 * @param bool Clear the identity from persistent storage?
	 * @return UserStorage Provides a fluent interface
	 */
	public function setExpiration($time, $whenBrowserIsClosed = TRUE, $clearIdentity = FALSE)
	{
		$session = $this->getSessionNamespace(TRUE);
		if ($time) {
			$time = Nette\Tools::createDateTime($time)->format('U');
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
	 * Why was user logged out?
	 * @return int
	 */
	public function getLogoutReason()
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
		if ($this->sessionNamespace !== NULL) {
			return $this->sessionNamespace;
		}

		if (!$need && !$this->sessionHandler->exists()) {
			return NULL;
		}

		$this->sessionNamespace = $session = $this->sessionHandler->getNamespace('Nette.Web.User/' . $this->namespace);

		if (!$session->identity instanceof IIdentity || !is_bool($session->authenticated)) {
			$session->remove();
		}

		if ($session->authenticated && $session->expireBrowser && !$session->browserCheck) { // check if browser was closed?
			$session->reason = self::BROWSER_CLOSED;
			$session->authenticated = FALSE;
			$this->onLoggedOut($this);
			if ($session->expireIdentity) {
				unset($session->identity);
			}
		}

		if ($session->authenticated && $session->expireDelta > 0) { // check time expiration
			if ($session->expireTime < time()) {
				$session->reason = self::INACTIVITY;
				$session->authenticated = FALSE;
				$this->onLoggedOut($this);
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

		return $this->sessionNamespace;
	}

	/**
	 * Sets the authenticated status of this user.
	 * @param bool Flag indicating the authenticated status of user
	 * @return UserStorage Provides a fluent interface
	 */
	protected function setAuthenticated($state)
	{
		$session = $this->getSessionNamespace(TRUE);
		$session->authenticated = (bool) $state;

		// Session Fixation defence
		$this->sessionHandler->regenerateId();

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
	 * @param IIdentity
	 * @return UserStorage Provides a fluent interface
	 */
	protected function setIdentity(IIdentity $identity = NULL)
	{
		$this->getSessionNamespace(TRUE)->identity = $identity;
		return $this;
	}
}
