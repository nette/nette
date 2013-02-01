<?php

/**
 * Test: Nette\Security\Permission Ensures that removal of a Role results in its rules being removed.
 *
 * @author     David Grudl
 * @author     Jachym Tousek
 * @package    Nette\Security
 */

use Nette\Security\Permission,
	Nette\Security\Identity;



require __DIR__ . '/../bootstrap.php';



$identity = new Identity(1, array('user'));
$acl = new Permission;
$acl->addRole('user');
$acl->allow('user');
Assert::true( $acl->isAllowed($identity) );
$acl->removeRole('user');
Assert::exception(function() use ($acl, $identity) {
	$acl->isAllowed($identity);
}, 'Nette\InvalidStateException', "Role 'user' does not exist.");

$acl->addRole('user');
Assert::false( $acl->isAllowed($identity) );
