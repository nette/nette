<?php

/**
 * Test: Nette\Security\User authorization.
 *
 * @author     David Grudl
 * @package    Nette\Http
 * @subpackage UnitTests
 */

use Nette\Security\IAuthenticator,
	Nette\Security\Identity,
	Nette\Security\IAuthorizator;



require __DIR__ . '/../bootstrap.php';



// Setup environment
$_COOKIE = array();
ob_start();



class Authenticator implements IAuthenticator
{
	/*
	 * @param  array
	 * @return IIdentity
	 * @throws Nette\Security\AuthenticationException
	 */
	function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;
		if ($username !== 'john') {
			throw new Nette\Security\AuthenticationException('Unknown user', self::IDENTITY_NOT_FOUND);

		} elseif ($password !== 'xxx') {
			throw new Nette\Security\AuthenticationException('Password not match', self::INVALID_CREDENTIAL);

		} else {
			return new Identity('John Doe', array('admin'));
		}
	}

}



class Authorizator implements IAuthorizator
{
	/**
	 * @param  string  role
	 * @param  string  resource
	 * @param  string  privilege
	 * @return bool
	 */
	function isAllowed($role = self::ALL, $resource = self::ALL, $privilege = self::ALL)
	{
		return $role === 'admin' && strpos($resource, 'jany') === FALSE;
	}

}



$user = Nette\Environment::getUser();

// guest
Assert::false( $user->isLoggedIn(), 'isLoggedIn?' );


Assert::same( array('guest'), $user->getRoles(), 'getRoles()' );
Assert::false( $user->isInRole('admin'), 'is admin?' );
Assert::true( $user->isInRole('guest'), 'is guest?' );



// authenticated
$handler = new Authenticator;
$user->setAuthenticator($handler);

// login as john
$user->login('john', 'xxx');

Assert::true( $user->isLoggedIn(), 'isLoggedIn?' );
Assert::same( array('admin'), $user->getRoles(), 'getRoles()' );
Assert::true( $user->isInRole('admin'), 'is admin?' );
Assert::false( $user->isInRole('guest'), 'is guest?' );


// authorization
Assert::throws(function() use ($user) {
	$user->isAllowed('delete_file');
}, 'Nette\InvalidStateException', 'Service of type Nette\Security\IAuthorizator not found.');

$handler = new Authorizator;
$user->setAuthorizator($handler);

Assert::true( $user->isAllowed('delete_file'), "isAllowed('delete_file')?" );
Assert::false( $user->isAllowed('sleep_with_jany'), "isAllowed('sleep_with_jany')?" );



// log out
// logging out...
$user->logout(FALSE);

Assert::false( $user->isAllowed('delete_file'), "isAllowed('delete_file')?" );
