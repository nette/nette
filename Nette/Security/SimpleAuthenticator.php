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
 * Trivial implementation of IAuthenticator.
 *
 * @author     David Grudl
 */
class SimpleAuthenticator extends Nette\Object implements IAuthenticator
{
	/** @var array */
	private $userlist;


	/**
	 * @param  array  list of pairs username => password
	 */
	public function __construct(array $userlist)
	{
		$this->userlist = $userlist;
	}



	/**
	 * Performs an authentication against e.g. database.
	 * and returns IIdentity on success or throws AuthenticationException
	 * @param  array
	 * @return IIdentity
	 * @throws AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;
		foreach ($this->userlist as $name => $pass) {
			if (strcasecmp($name, $username) === 0) {
				if ($pass === $password) {
					return new Identity($name);
				} else {
					throw new AuthenticationException("Invalid password.", self::INVALID_CREDENTIAL);
				}
			}
		}
		throw new AuthenticationException("User '$username' not found.", self::IDENTITY_NOT_FOUND);
	}

}
