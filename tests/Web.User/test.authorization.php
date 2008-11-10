<?php ob_start(); ?>
<h1>Nette\Web\User Authorization test</h1>

<pre>
<?php
require_once '../../Nette/loader.php';

/*use Nette\Debug;*/
/*use Nette\Web\User;*/
/*use Nette\Security\IAuthenticator;*/
/*use Nette\Security\AuthenticationException;*/
/*use Nette\Security\Identity;*/
/*use Nette\Security\IAuthorizator;*/


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


// delete cookies
$_COOKIE = array();

$user = new User;

// guest
echo "isAuthenticated?\n";
Debug::dump($user->isAuthenticated());

echo "getRoles()\n";
Debug::dump($user->getRoles());

echo "is admin?\n";
Debug::dump($user->isInRole('admin'));

echo "is guest?\n";
Debug::dump($user->isInRole('guest'));


// authenticated
$handler = new AuthenticationHandler;
$user->setAuthenticationHandler($handler);

echo "authenticate as john\n";
$user->authenticate('john', 'xxx');

echo "isAuthenticated?\n";
Debug::dump($user->isAuthenticated());

echo "getRoles()\n";
Debug::dump($user->getRoles());

echo "is admin?\n";
Debug::dump($user->isInRole('admin'));

echo "is guest?\n";
Debug::dump($user->isInRole('guest'));



// authorization
try {
	echo "authorize without handler\n";
	Debug::dump($user->isAllowed('delete_file'));
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}

$handler = new AuthorizationHandler;
$user->setAuthorizationHandler($handler);

echo "isAllowed('delete_file')?\n";
Debug::dump($user->isAllowed('delete_file'));

echo "isAllowed('sleep_with_jany')?\n";
Debug::dump($user->isAllowed('sleep_with_jany'));


// sign out
echo "signing out...\n";
$user->signOut(FALSE);

echo "isAllowed('delete_file')?\n";
Debug::dump($user->isAllowed('delete_file'));
