<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 */

namespace Nette\Security;

use Nette;



/**
 * Performs authentication.
 *
 * @author     David Grudl
 */
interface IAuthenticator
{
	/**#@+ Credential key */
	const USERNAME = 0;
	const PASSWORD = 1;
	/**#@-*/

	/**#@+ Exception error code */
	const IDENTITY_NOT_FOUND = 1;
	const INVALID_CREDENTIAL = 2;
	const FAILURE = 3;
	const NOT_APPROVED = 4;
	/**#@-*/

	/**
	 * Performs an authentication against e.g. database.
	 * and returns IIdentity on success or throws AuthenticationException
	 * @param  array
	 * @return IIdentity
	 * @throws AuthenticationException
	 */
	function authenticate(array $credentials);

}
