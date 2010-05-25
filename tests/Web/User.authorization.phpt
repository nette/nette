<?php

/**
 * Test: Nette\Web\User authorization.
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
/*use Nette\Security\IAuthorizator;*/



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

		return new Identity('John Doe', array('admin'));
	}

}



class AuthorizationHandler implements IAuthorizator
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



$user = new User;

// guest
dump( $user->isLoggedIn(), "isLoggedIn?" );

dump( $user->getRoles(), "getRoles()" );

dump( $user->isInRole('admin'), "is admin?" );

dump( $user->isInRole('guest'), "is guest?" );


// authenticated
$handler = new AuthenticationHandler;
$user->setAuthenticationHandler($handler);

output("login as john");
$user->login('john', 'xxx');

dump( $user->isLoggedIn(), "isLoggedIn?" );

dump( $user->getRoles(), "getRoles()" );

dump( $user->isInRole('admin'), "is admin?" );

dump( $user->isInRole('guest'), "is guest?" );



// authorization
try {
	dump( $user->isAllowed('delete_file'), "authorize without handler" );
} catch (Exception $e) {
	dump( $e );
}

$handler = new AuthorizationHandler;
$user->setAuthorizationHandler($handler);

dump( $user->isAllowed('delete_file'), "isAllowed('delete_file')?" );

dump( $user->isAllowed('sleep_with_jany'), "isAllowed('sleep_with_jany')?" );


// log out
output("logging out...");
$user->logout(FALSE);

dump( $user->isAllowed('delete_file'), "isAllowed('delete_file')?" );



__halt_compiler() ?>

------EXPECT------
isLoggedIn? bool(FALSE)

getRoles(): array(1) {
	0 => string(5) "guest"
}

is admin? bool(FALSE)

is guest? bool(TRUE)

login as john

isLoggedIn? bool(TRUE)

getRoles(): array(1) {
	0 => string(5) "admin"
}

is admin? bool(TRUE)

is guest? bool(FALSE)

Exception InvalidStateException: Service 'Nette\Security\IAuthorizator' not found.

isAllowed('delete_file')? bool(TRUE)

isAllowed('sleep_with_jany')? bool(FALSE)

logging out...

isAllowed('delete_file')? bool(FALSE)
