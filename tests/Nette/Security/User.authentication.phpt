<?php

/**
 * Test: Nette\Security\User authentication.
 *
 * @author     David Grudl
 * @package    Nette\Http
 * @subpackage UnitTests
 */

use Nette\Security\IAuthenticator,
	Nette\Security\Identity;



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
			return new Identity('John Doe', 'admin');
		}
	}

}



function onLoggedIn($user) {
	// TODO: add test
}



function onLoggedOut($user) {
	// TODO: add test
}



$user = Nette\Environment::getUser();
$user->onLoggedIn[] = 'onLoggedIn';
$user->onLoggedOut[] = 'onLoggedOut';


Assert::false( $user->isLoggedIn(), 'isLoggedIn?' );
Assert::null( $user->getIdentity(), 'getIdentity' );
Assert::null( $user->getId(), 'getId' );



// authenticate
Assert::throws(function() use ($user) {
	// login without handler
	$user->login('jane', '');
}, 'Nette\InvalidStateException', 'Service of type Nette\Security\IAuthenticator not found.');

$handler = new Authenticator;
$user->setAuthenticator($handler);

Assert::throws(function() use ($user) {
	// login as jane
	$user->login('jane', '');
}, 'Nette\Security\AuthenticationException', 'Unknown user');

Assert::throws(function() use ($user) {
	// login as john
	$user->login('john', '');
}, 'Nette\Security\AuthenticationException', 'Password not match');

// login as john#2
$user->login('john', 'xxx');
Assert::true( $user->isLoggedIn(), 'isLoggedIn?' );
Assert::equal( new Identity('John Doe', 'admin'), $user->getIdentity(), 'getIdentity' );
Assert::same( 'John Doe', $user->getId(), 'getId' );




// log out
// logging out...
$user->logout(FALSE);

Assert::false( $user->isLoggedIn(), 'isLoggedIn?' );
Assert::equal( new Identity('John Doe', 'admin'), $user->getIdentity(), 'getIdentity' );


// logging out and clearing identity...
$user->logout(TRUE);

Assert::false( $user->isLoggedIn(), 'isLoggedIn?' );
Assert::null( $user->getIdentity(), 'getIdentity' );




// namespace
// login as john#2?
$user->login('john', 'xxx');
Assert::true( $user->isLoggedIn(), 'isLoggedIn?' );


// setNamespace(...)
$user->setNamespace('other');

Assert::false( $user->isLoggedIn(), 'isLoggedIn?' );
Assert::null( $user->getIdentity(), 'getIdentity' );
