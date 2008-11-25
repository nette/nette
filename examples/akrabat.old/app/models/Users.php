<?php

/*use Nette\Security\AuthenticationException;*/


/**
 * Users
 *
 * @sql
 *  CREATE TABLE [users] (
 *  [id] INTEGER  NULL PRIMARY KEY,
 *  [username] VARCHAR(50)  UNIQUE NOT NULL,
 *  [password] VARCHAR(50)  NOT NULL,
 *  [real_name] VARCHAR(100)  NOT NULL
 *  );
 */
class Users extends DibiTableX implements /*Nette\Security\*/IAuthenticator
{

	/**
	 * Performs an authentication
	 * @param  array
	 * @return void
	 * @throws AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		$username = $credentials[self::USERNAME];
		$row = $this->fetch(array('username' => $username));
		if (!$row) {
			throw new AuthenticationException("User '$username' not found.", self::IDENTITY_NOT_FOUND);
		}

		if ($row->password !== $credentials[self::PASSWORD]) {
			throw new AuthenticationException("Invalid password.", self::INVALID_CREDENTIAL);
		}

		unset($row->password);
		return new /*Nette\Security\*/Identity($row->username, array(), $row);
	}

}
