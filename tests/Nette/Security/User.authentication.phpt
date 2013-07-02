<?php

/**
 * Test: Nette\Security\User authentication.
 *
 * @author     David Grudl
 * @package    Nette\Http
 */

use Nette\Security\IAuthenticator,
	Nette\Security\Identity;


require __DIR__ . '/../bootstrap.php';


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


function onLoggedIn($user) {
	// TODO: add test
}


function onLoggedOut($user) {
	// TODO: add test
}


$container = id(new Nette\Configurator)->setTempDirectory(TEMP_DIR)->createContainer();

$user = $container->getService('user');
$user->onLoggedIn[] = 'onLoggedIn';
$user->onLoggedOut[] = 'onLoggedOut';


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
Assert::true( $user->isLoggedIn() );
Assert::equal( new Identity('John Doe', 'admin'), $user->getIdentity() );
Assert::same( 'John Doe', $user->getId() );

// login as john#3
$user->logout(TRUE);
$user->login( new Identity('John Doe', 'admin') );
Assert::true( $user->isLoggedIn() );
Assert::equal( new Identity('John Doe', 'admin'), $user->getIdentity() );


// log out
// logging out...
$user->logout(FALSE);

Assert::false( $user->isLoggedIn() );
Assert::equal( new Identity('John Doe', 'admin'), $user->getIdentity() );


// logging out and clearing identity...
$user->logout(TRUE);

Assert::false( $user->isLoggedIn() );
Assert::null( $user->getIdentity() );


// namespace
// login as john#2?
$user->login('john', 'xxx');
Assert::true( $user->isLoggedIn() );


// setNamespace(...)
$user->getStorage()->setNamespace('other');

Assert::false( $user->isLoggedIn() );
Assert::null( $user->getIdentity() );
