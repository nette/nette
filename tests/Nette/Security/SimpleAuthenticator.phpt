<?php

/**
 * Test: Nette\Security\SimpleAuthenticator
 *
 * @author     Matěj Koubík
 * @package    Nette\Security
 */

use Nette\Security\SimpleAuthenticator;



require __DIR__ . '/../bootstrap.php';



$users = array(
	'john' => 'password123!',
	'admin' => 'admin',
);

$authenticator = new SimpleAuthenticator($users);

$identity = $authenticator->authenticate(array('john', 'password123!'));
Assert::true($identity instanceof Nette\Security\IIdentity);
Assert::equal('john', $identity->getId());

$identity = $authenticator->authenticate(array('admin', 'admin'));
Assert::true($identity instanceof Nette\Security\IIdentity);
Assert::equal('admin', $identity->getId());

Assert::exception(function() use ($authenticator) {
	$authenticator->authenticate(array('admin', 'wrong password'));
}, 'Nette\Security\AuthenticationException', 'Invalid password.');

Assert::exception(function() use ($authenticator) {
	$authenticator->authenticate(array('nobody', 'password'));
}, 'Nette\Security\AuthenticationException', "User 'nobody' not found.");
