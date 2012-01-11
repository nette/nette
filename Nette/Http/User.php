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
	Nette\Security\IUserStorage;



/**
 * @deprecated
 * @phpversion 5.3
 */
class User extends Nette\Security\User
{
	const MANUAL = IUserStorage::MANUAL,
		INACTIVITY = IUserStorage::INACTIVITY,
		BROWSER_CLOSED = IUserStorage::BROWSER_CLOSED;

}
