<?php

/**
 * Test: Nette\Web\User authentication.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Web
 * @subpackage UnitTests
 */

use Nette\Web\User,
	Nette\Security\IAuthenticator,
	Nette\Security\AuthenticationException,
	Nette\Security\Identity;



require __DIR__ . '/../initialize.php';



// Setup environment
$_COOKIE = array();
ob_start();



class AuthenticationHandler implements IAuthenticator
{
	/*
	 * @param  array
	 * @return IIdentity
	 * @throws AuthenticationException
	 */
	function authenticate(array $credentials)
	{
		if ($credentials['username'] !== 'john') {
			throw new AuthenticationException('Unknown user', self::IDENTITY_NOT_FOUND);
		}

		if ($credentials['password'] !== 'xxx') {
			throw new AuthenticationException('Password not match', self::INVALID_CREDENTIAL);
		}

		return new Identity('John Doe', 'admin');
	}

}



function onLoggedIn($user) {
	T::note("[onLoggedIn]");
}



function onLoggedOut($user) {
	echo "\n[onLoggedOut $user->logoutReason]\n";
}



$user = new User;
$user->onLoggedIn[] = 'onLoggedIn';
$user->onLoggedOut[] = 'onLoggedOut';


T::dump( $user->isLoggedIn(), "isLoggedIn?" );

T::dump( $user->getIdentity(), "getIdentity" );

T::dump( $user->getId(), "getId" );


// authenticate
try {
	T::note("login without handler");
	$user->login('jane', '');
} catch (Exception $e) {
	T::dump( $e );
}

$handler = new AuthenticationHandler;
$user->setAuthenticationHandler($handler);

try {
	T::note("login as jane");
	$user->login('jane', '');
} catch (Exception $e) {
	T::dump( $e );
}

try {
	T::note("login as john");
	$user->login('john', '');
} catch (Exception $e) {
	T::dump( $e );
}

try {
	T::note("login as john#2");
	$user->login('john', 'xxx');
} catch (Exception $e) {
	T::dump( $e );
}

T::dump( $user->isLoggedIn(), "isLoggedIn?" );

T::dump( $user->getIdentity(), "getIdentity" );

T::dump( $user->getId(), "getId" );



// log out
T::note("logging out...");
$user->logout(FALSE);

T::dump( $user->isLoggedIn(), "isLoggedIn?" );

T::dump( $user->getIdentity(), "getIdentity" );

T::note("logging out and clearing identity...");
$user->logout(TRUE);

T::dump( $user->isLoggedIn(), "isLoggedIn?" );

T::dump( $user->getIdentity(), "getIdentity" );



// namespace
T::note("login as john#2?");
$user->login('john', 'xxx');

T::dump( $user->isLoggedIn(), "isLoggedIn?" );

T::note("setNamespace(...)");
$user->setNamespace('other');

T::dump( $user->isLoggedIn(), "isLoggedIn?" );

T::dump( $user->getIdentity(), "getIdentity" );



__halt_compiler() ?>

------EXPECT------
isLoggedIn? FALSE

getIdentity: NULL

getId: NULL

login without handler

Exception InvalidStateException: Service 'Nette\Security\IAuthenticator' not found.

login as jane

Exception %ns%AuthenticationException: #1 Unknown user

login as john

Exception %ns%AuthenticationException: #2 Password not match

login as john#2

[onLoggedIn]

isLoggedIn? TRUE

getIdentity: %ns%Identity(
	"id" private => "John Doe"
	"roles" private => array(
		"admin"
	)
	"data" private => array()
	"frozen" private => FALSE
)

getId: "John Doe"

logging out...


[onLoggedOut 1]
isLoggedIn? FALSE

getIdentity: %ns%Identity(
	"id" private => "John Doe"
	"roles" private => array(
		"admin"
	)
	"data" private => array()
	"frozen" private => FALSE
)

logging out and clearing identity...

isLoggedIn? FALSE

getIdentity: NULL

login as john#2?

[onLoggedIn]

isLoggedIn? TRUE

setNamespace(...)

isLoggedIn? FALSE

getIdentity: NULL
