<?php

use Nette\Object,
	Nette\Security\AuthenticationException;


/**
 * Users authenticator.
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
		$row = dibi::select('*')->from('users')->where('username=%s', $username)->fetch();

		if (!$row) {
			throw new AuthenticationException("User '$username' not found.", self::IDENTITY_NOT_FOUND);
		}

		if ($row->password !== $this->calculateHash($password)) {
			throw new AuthenticationException("Invalid password.", self::INVALID_CREDENTIAL);
		}

		unset($row->password);
		return new Nette\Security\Identity($row->id, NULL, $row);
	}



	/**
	 * Computes salted password hash.
	 * @param  string
	 * @return string
	 */
	public function calculateHash($password)
	{
		return md5($password . str_repeat('*random salt*', 10));
	}

}
