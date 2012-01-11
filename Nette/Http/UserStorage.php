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
	Nette\Security\IIdentity;



/**
 * Session storage for user object.
 *
 * @author David Grudl, Jan Tichý
 */
class UserStorage extends Nette\Object implements Nette\Security\IUserStorage
{
	/** @var string */
	private $namespace = '';

	/** @var Session */
	private $sessionHandler;

	/** @var SessionSection */
	private $sessionSection;



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
		$session = $this->getSessionSection(FALSE);
		return $session && $session->authenticated;
	}



	/**
	 * Returns current user identity, if any.
	 * @return Nette\Security\IIdentity
	 */
	public function getIdentity()
	{
		$session = $this->getSessionSection(FALSE);
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
			$this->sessionSection = NULL;
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
	public function getLogoutReason()
	{
		$session = $this->getSessionSection(FALSE);
		return $session ? $session->reason : NULL;
	}



	/**
	 * Returns and initializes $this->sessionSection.
	 * @return SessionSection
	 */
	protected function getSessionSection($need)
	{
		if ($this->sessionSection !== NULL) {
			return $this->sessionSection;
		}

		if (!$need && !$this->sessionHandler->exists()) {
			return NULL;
		}

		$this->sessionSection = $section = $this->sessionHandler->getSection('Nette.Http.UserStorage/' . $this->namespace);

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

		return $this->sessionSection;
	}



	/**
	 * Sets the authenticated status of this user.
	 * @param bool Flag indicating the authenticated status of user
	 * @return UserStorage Provides a fluent interface
	 */
	protected function setAuthenticated($state)
	{
		$section = $this->getSessionSection(TRUE);
		$section->authenticated = (bool) $state;

		// Session Fixation defence
		$this->sessionHandler->regenerateId();

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
	 * @param IIdentity
	 * @return UserStorage Provides a fluent interface
	 */
	protected function setIdentity(IIdentity $identity = NULL)
	{
		$this->getSessionSection(TRUE)->identity = $identity;
		return $this;
	}

}
