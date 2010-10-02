<?php

/**
 * My Application
 *
 * @copyright  Copyright (c) 2010 John Doe
 * @package    MyApplication
 */

use Nette\Object;
use Nette\Security\AuthenticationException;


/**
 * Users authenticator.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class UsersModel extends Object implements Nette\Security\IAuthenticator
{

	/**
	 * Performs an authentication
	 * @param  array
	 * @return IIdentity
	 * @throws AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;
		$row = dibi::fetch('SELECT * FROM users WHERE login=%s', $username);

		if (!$row) {
			throw new AuthenticationException("User '$username' not found.", self::IDENTITY_NOT_FOUND);
		}

		if ($row->password !== $this->calculateHash($password)) {
			throw new AuthenticationException("Invalid password.", self::INVALID_CREDENTIAL);
		}

		unset($row->password);
		return new Nette\Security\Identity($row->id, $row->role, $row);
	}



	/**
	 * Computes salted password hash.
	 * @param  string
	 * @return string
	 */
	public function calculateHash($password)
	{
		return md5($password . str_repeat('*enter any random salt here*', 10));
	}

}
