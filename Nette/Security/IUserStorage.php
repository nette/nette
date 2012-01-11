<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Security;

use Nette;



/**
 * Interface for persistent storage for user object data.
 *
 * @author David Grudl, Jan Tichý
 */
interface IUserStorage
{
	/** Log-out reason {@link IUserStorage::getLogoutReason()} */
	const MANUAL = 1,
		INACTIVITY = 2,
		BROWSER_CLOSED = 3;

	/**
	 * Logs in the user to the persistent storage.
	 * @param IIdentity
	 * @return IUserStorage Provides a fluent interface
	 */
	function login(IIdentity $identity);

	/**
	 * Logs out the user from the persistent storage.
	 * @param bool Clear the identity from persistent storage?
	 * @return IUserStorage Provides a fluent interface
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
	 * Enables log out from the persistent storage after inactivity.
	 * @param string|int|DateTime number of seconds or timestamp
	 * @param bool Log out when the browser is closed?
	 * @param bool Clear the identity from persistent storage?
	 * @return IUserStorage Provides a fluent interface
	 */
	function setExpiration($time, $whenBrowserIsClosed = TRUE, $clearIdentity = FALSE);

	/**
	 * Why was user logged out?
	 * @return int
	 */
	function getLogoutReason();

}
