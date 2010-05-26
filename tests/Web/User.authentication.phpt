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



require __DIR__ . '/../NetteTest/initialize.php';



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
	output("[onLoggedIn]");
}



function onLoggedOut($user) {
	echo "\n[onLoggedOut $user->logoutReason]\n";
}



$user = new User;
$user->onLoggedIn[] = 'onLoggedIn';
$user->onLoggedOut[] = 'onLoggedOut';


dump( $user->isLoggedIn(), "isLoggedIn?" );

dump( $user->getIdentity(), "getIdentity" );

dump( $user->getId(), "getId" );


// authenticate
try {
	output("login without handler");
	$user->login('jane', '');
} catch (Exception $e) {
	dump( $e );
}

$handler = new AuthenticationHandler;
$user->setAuthenticationHandler($handler);

try {
	output("login as jane");
	$user->login('jane', '');
} catch (Exception $e) {
	dump( $e );
}

try {
	output("login as john");
	$user->login('john', '');
} catch (Exception $e) {
	dump( $e );
}

try {
	output("login as john#2");
	$user->login('john', 'xxx');
} catch (Exception $e) {
	dump( $e );
}

dump( $user->isLoggedIn(), "isLoggedIn?" );

dump( $user->getIdentity(), "getIdentity" );

dump( $user->getId(), "getId" );



// log out
output("logging out...");
$user->logout(FALSE);

dump( $user->isLoggedIn(), "isLoggedIn?" );

dump( $user->getIdentity(), "getIdentity" );

output("logging out and clearing identity...");
$user->logout(TRUE);

dump( $user->isLoggedIn(), "isLoggedIn?" );

dump( $user->getIdentity(), "getIdentity" );



// namespace
output("login as john#2?");
$user->login('john', 'xxx');

dump( $user->isLoggedIn(), "isLoggedIn?" );

output("setNamespace(...)");
$user->setNamespace('other');

dump( $user->isLoggedIn(), "isLoggedIn?" );

dump( $user->getIdentity(), "getIdentity" );



__halt_compiler() ?>

------EXPECT------
isLoggedIn? bool(FALSE)

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

isLoggedIn? bool(TRUE)

getIdentity: object(%ns%Identity) (4) {
	"id" private => string(8) "John Doe"
	"roles" private => array(1) {
		0 => string(5) "admin"
	}
	"data" private => array(0)
	"frozen" private => bool(FALSE)
}

getId: string(8) "John Doe"

logging out...


[onLoggedOut 1]
isLoggedIn? bool(FALSE)

getIdentity: object(%ns%Identity) (4) {
	"id" private => string(8) "John Doe"
	"roles" private => array(1) {
		0 => string(5) "admin"
	}
	"data" private => array(0)
	"frozen" private => bool(FALSE)
}

logging out and clearing identity...

isLoggedIn? bool(FALSE)

getIdentity: NULL

login as john#2?

[onLoggedIn]

isLoggedIn? bool(TRUE)

setNamespace(...)

isLoggedIn? bool(FALSE)

getIdentity: NULL
