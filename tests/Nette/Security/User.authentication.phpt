<?php

/**
 * Test: Nette\Security\User authentication.
 */

use Nette\Security\IAuthenticator,
	Nette\Security\Identity,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/MockUserStorage.php';

// Setup environment
$_COOKIE = array();
ob_start();


class Authenticator implements IAuthenticator
{
	/**
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
			return new Identity('John Doe', 'admin');
		}
	}

}


$user = new Nette\Security\User(new MockUserStorage);

$counter = (object) array(
	'login' => 0,
	'logout' => 0,
);

$user->onLoggedIn[] = function () use ($counter) {
	$counter->login++;
};

$user->onLoggedOut[] = function () use ($counter) {
	$counter->logout++;
};


Assert::false( $user->isLoggedIn() );
Assert::null( $user->getIdentity() );
Assert::null( $user->getId() );


// authenticate
Assert::exception(function() use ($user) {
	// login without handler
	$user->login('jane', '');
}, 'Nette\InvalidStateException', 'Authenticator has not been set.');

$handler = new Authenticator;
$user->setAuthenticator($handler);

Assert::exception(function() use ($user) {
	// login as jane
	$user->login('jane', '');
}, 'Nette\Security\AuthenticationException', 'Unknown user');

Assert::exception(function() use ($user) {
	// login as john
	$user->login('john', '');
}, 'Nette\Security\AuthenticationException', 'Password not match');

// login as john#2
$user->login('john', 'xxx');
Assert::same( 1, $counter->login );
Assert::true( $user->isLoggedIn() );
Assert::equal( new Identity('John Doe', 'admin'), $user->getIdentity() );
Assert::same( 'John Doe', $user->getId() );

// login as john#3
$user->logout(TRUE);
Assert::same( 1, $counter->logout );
$user->login( new Identity('John Doe', 'admin') );
Assert::same( 2, $counter->login );
Assert::true( $user->isLoggedIn() );
Assert::equal( new Identity('John Doe', 'admin'), $user->getIdentity() );


// log out
// logging out...
$user->logout(FALSE);
Assert::same( 2, $counter->logout );

Assert::false( $user->isLoggedIn() );
Assert::equal( new Identity('John Doe', 'admin'), $user->getIdentity() );


// logging out and clearing identity...
$user->logout(TRUE);
Assert::same( 2, $counter->logout ); // not logged in -> logout event not triggered

Assert::false( $user->isLoggedIn() );
Assert::null( $user->getIdentity() );


// namespace
// login as john#2?
$user->login('john', 'xxx');
Assert::same( 3, $counter->login );
Assert::true( $user->isLoggedIn() );
