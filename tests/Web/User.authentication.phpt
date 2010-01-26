<?php

/**
 * Test: Nette\Web\User authentication.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Web
 * @subpackage UnitTests
 */

/*use Nette\Web\User;*/
/*use Nette\Security\IAuthenticator;*/
/*use Nette\Security\AuthenticationException;*/
/*use Nette\Security\Identity;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



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



function onAuthenticated($user) {
	output("[onAuthenticated]");
}



function onSignedOut($user) {
	echo "\n[onSignedOut $user->signOutReason]\n";
}



$user = new User;
$user->onAuthenticated[] = 'onAuthenticated';
$user->onSignedOut[] = 'onSignedOut';


dump( $user->isAuthenticated(), "isAuthenticated?" );

dump( $user->getIdentity(), "getIdentity" );

dump( $user->getId(), "getId" );


// authenticate
try {
	output("authenticate without handler");
	$user->authenticate('jane', '');
} catch (Exception $e) {
	dump( $e );
}

$handler = new AuthenticationHandler;
$user->setAuthenticationHandler($handler);

try {
	output("authenticate as jane");
	$user->authenticate('jane', '');
} catch (Exception $e) {
	dump( $e );
}

try {
	output("authenticate as john");
	$user->authenticate('john', '');
} catch (Exception $e) {
	dump( $e );
}

try {
	output("authenticate as john#2");
	$user->authenticate('john', 'xxx');
} catch (Exception $e) {
	dump( $e );
}

dump( $user->isAuthenticated(), "isAuthenticated?" );

dump( $user->getIdentity(), "getIdentity" );

dump( $user->getId(), "getId" );



// sign out
output("signing out...");
$user->signOut(FALSE);

dump( $user->isAuthenticated(), "isAuthenticated?" );

dump( $user->getIdentity(), "getIdentity" );

output("signing out and clearing identity...");
$user->signOut(TRUE);

dump( $user->isAuthenticated(), "isAuthenticated?" );

dump( $user->getIdentity(), "getIdentity" );



// namespace
output("authenticate as john#2?");
$user->authenticate('john', 'xxx');

dump( $user->isAuthenticated(), "isAuthenticated?" );

output("setNamespace(...)");
$user->setNamespace('other');

dump( $user->isAuthenticated(), "isAuthenticated?" );

dump( $user->getIdentity(), "getIdentity" );



__halt_compiler();

------EXPECT------
isAuthenticated? bool(FALSE)

getIdentity: NULL

getId: NULL

authenticate without handler

Exception InvalidStateException: Service 'Nette\Security\IAuthenticator' not found.

authenticate as jane

Exception %ns%AuthenticationException: #1 Unknown user

authenticate as john

Exception %ns%AuthenticationException: #2 Password not match

authenticate as john#2

[onAuthenticated]

isAuthenticated? bool(TRUE)

getIdentity: object(%ns%Identity) (4) {
	"id" private => string(8) "John Doe"
	"roles" private => array(1) {
		0 => string(5) "admin"
	}
	"data" private => array(0)
	"frozen" private => bool(FALSE)
}

getId: string(8) "John Doe"

signing out...


[onSignedOut 1]
isAuthenticated? bool(FALSE)

getIdentity: object(%ns%Identity) (4) {
	"id" private => string(8) "John Doe"
	"roles" private => array(1) {
		0 => string(5) "admin"
	}
	"data" private => array(0)
	"frozen" private => bool(FALSE)
}

signing out and clearing identity...

isAuthenticated? bool(FALSE)

getIdentity: NULL

authenticate as john#2?

[onAuthenticated]

isAuthenticated? bool(TRUE)

setNamespace(...)

isAuthenticated? bool(FALSE)

getIdentity: NULL
