<?php ob_start(); ?>
<h1>Nette\Web\User Authentication test</h1>

<pre>
<?php
require_once '../../Nette/loader.php';

/*use Nette\Debug;*/
/*use Nette\Web\User;*/
/*use Nette\Security\IAuthenticator;*/
/*use Nette\Security\AuthenticationException;*/
/*use Nette\Security\Identity;*/


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
	echo "\n[onAuthenticated]\n";
}

function onSignedOut($user) {
	echo "\n[onSignedOut $user->signOutReason]\n";
}


// delete cookies
$_COOKIE = array();

$user = new User;
$user->onAuthenticated[] = 'onAuthenticated';
$user->onSignedOut[] = 'onSignedOut';


echo "isAuthenticated?\n";
Debug::dump($user->isAuthenticated());

echo "getIdentity\n";
Debug::dump($user->getIdentity());


// authenticate
try {
	echo "authenticate without handler\n";
	$user->authenticate('jane', '');
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}

$handler = new AuthenticationHandler;
$user->setAuthenticationHandler($handler);

try {
	echo "authenticate as jane\n";
	$user->authenticate('jane', '');
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), ' #', $e->getCode(), "\n\n";
}

try {
	echo "authenticate as john\n";
	$user->authenticate('john', '');
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), ' #', $e->getCode(), "\n\n";
}

try {
	echo "authenticate as john#2\n";
	$user->authenticate('john', 'xxx');
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), ' #', $e->getCode(), "\n\n";
}

echo "isAuthenticated?\n";
Debug::dump($user->isAuthenticated());

echo "getIdentity\n";
Debug::dump($user->getIdentity());



// sign out
echo "signing out...\n";
$user->signOut(FALSE);

echo "isAuthenticated?\n";
Debug::dump($user->isAuthenticated());

echo "getIdentity\n";
Debug::dump($user->getIdentity());

echo "signing out and clearing identity...\n";
$user->signOut(TRUE);

echo "isAuthenticated?\n";
Debug::dump($user->isAuthenticated());

echo "getIdentity\n";
Debug::dump($user->getIdentity());



// namespace
echo "authenticate as john#2?\n";
$user->authenticate('john', 'xxx');

echo "isAuthenticated?\n";
Debug::dump($user->isAuthenticated());

echo "setNamespace(...)\n";
$user->setNamespace('other');

echo "isAuthenticated?\n";
Debug::dump($user->isAuthenticated());

echo "getIdentity\n";
Debug::dump($user->getIdentity());
