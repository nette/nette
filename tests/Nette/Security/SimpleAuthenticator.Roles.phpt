<?php

/**
 * Test: Nette\Security\SimpleAuthenticator and roles
 *
 * @author     David Matejka
 */

use Nette\Security\SimpleAuthenticator,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$users = array(
	'john'  => 'john123',
	'admin' => 'admin123',
	'user'  => 'user123',
);
$usersRoles = array(
	'admin' => array('admin', 'user'),
	'user'  => 'user',
);
$expectedRoles = array(
	'admin' => array('admin', 'user'),
	'user'  => array('user'),
	'john'  => array(),
);

$authenticator = new SimpleAuthenticator($users, $usersRoles);

foreach ($users as $username => $password) {
	$identity = $authenticator->authenticate(array($username, $password));
	Assert::equal($username, $identity->getId());
	Assert::equal($expectedRoles[$username], $identity->getRoles());
}
