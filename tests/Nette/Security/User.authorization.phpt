<?php

/**
 * Test: Nette\Security\User authorization.
 *
 * @author     David Grudl
 * @package    Nette\Http
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


$container = id(new Nette\Configurator)->setTempDirectory(TEMP_DIR)->createContainer();

$user = $container->getService('user');

// guest
Assert::false( $user->isLoggedIn() );


Assert::same( array('guest'), $user->getRoles() );
Assert::false( $user->isInRole('admin') );
Assert::true( $user->isInRole('guest') );


// authenticated
$handler = new Authenticator;
$user->setAuthenticator($handler);

// login as john
$user->login('john', 'xxx');

Assert::true( $user->isLoggedIn() );
Assert::same( array('admin'), $user->getRoles() );
Assert::true( $user->isInRole('admin') );
Assert::false( $user->isInRole('guest') );


// authorization
Assert::exception(function() use ($user) {
	$user->isAllowed('delete_file');
}, 'Nette\InvalidStateException', 'Authorizator has not been set.');

$handler = new Authorizator;
$user->setAuthorizator($handler);

Assert::true( $user->isAllowed('delete_file') );
Assert::false( $user->isAllowed('sleep_with_jany') );


// log out
// logging out...
$user->logout(FALSE);

Assert::false( $user->isAllowed('delete_file') );
