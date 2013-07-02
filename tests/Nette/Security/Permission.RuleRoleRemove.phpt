<?php

/**
 * Test: Nette\Security\Permission Ensures that removal of a Role results in its rules being removed.
 *
 * @author     David Grudl
 * @package    Nette\Security
 */

use Nette\Security\Permission;


require __DIR__ . '/../bootstrap.php';


$acl = new Permission;
$acl->addRole('guest');
$acl->allow('guest');
Assert::true( $acl->isAllowed('guest') );
$acl->removeRole('guest');
Assert::exception(function() use ($acl) {
	$acl->isAllowed('guest');
}, 'Nette\InvalidStateException', "Role 'guest' does not exist.");

$acl->addRole('guest');
Assert::false( $acl->isAllowed('guest') );
