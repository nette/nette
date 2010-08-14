<?php

/**
 * Test: Nette\Web\User authorization.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Web
 * @subpackage UnitTests
 */

use Nette\Web\User,
	Nette\Security\IAuthenticator,
	Nette\Security\AuthenticationException,
	Nette\Security\Identity,
	Nette\Security\IAuthorizator;



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
		if ($credentials[self::USERNAME] !== 'john') {
			throw new AuthenticationException('Unknown user', self::IDENTITY_NOT_FOUND);
		}

		if ($credentials[self::PASSWORD] !== 'xxx') {
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
T::dump( $user->isLoggedIn(), "isLoggedIn?" );

T::dump( $user->getRoles(), "getRoles()" );

T::dump( $user->isInRole('admin'), "is admin?" );

T::dump( $user->isInRole('guest'), "is guest?" );


// authenticated
$handler = new AuthenticationHandler;
$user->setAuthenticationHandler($handler);

T::note("login as john");
$user->login('john', 'xxx');

T::dump( $user->isLoggedIn(), "isLoggedIn?" );

T::dump( $user->getRoles(), "getRoles()" );

T::dump( $user->isInRole('admin'), "is admin?" );

T::dump( $user->isInRole('guest'), "is guest?" );



// authorization
try {
	T::dump( $user->isAllowed('delete_file'), "authorize without handler" );
} catch (Exception $e) {
	T::dump( $e );
}

$handler = new AuthorizationHandler;
$user->setAuthorizationHandler($handler);

T::dump( $user->isAllowed('delete_file'), "isAllowed('delete_file')?" );

T::dump( $user->isAllowed('sleep_with_jany'), "isAllowed('sleep_with_jany')?" );


// log out
T::note("logging out...");
$user->logout(FALSE);

T::dump( $user->isAllowed('delete_file'), "isAllowed('delete_file')?" );



__halt_compiler() ?>

------EXPECT------
isLoggedIn? FALSE

getRoles(): array(
	"guest"
)

is admin? FALSE

is guest? TRUE

login as john

isLoggedIn? TRUE

getRoles(): array(
	"admin"
)

is admin? TRUE

is guest? FALSE

Exception InvalidStateException: Service 'Nette\Security\IAuthorizator' not found.

isAllowed('delete_file')? TRUE

isAllowed('sleep_with_jany')? FALSE

logging out...

isAllowed('delete_file')? FALSE
