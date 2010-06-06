<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette\Security
 */

namespace Nette\Security;

use Nette;



/**
 * Performs authentication.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Security
 */
interface IAuthenticator
{
	/**#@+ Credential key */
	const USERNAME = 'username';
	const PASSWORD = 'password';
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
