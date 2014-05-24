<?php

/**
 * Test: Nette\Security\Permission Ensures that removal of all Roles works.
 */

use Nette\Security\Permission,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$acl = new Permission;
$acl->addRole('guest');
$acl->removeAllRoles();
Assert::false( $acl->hasRole('guest') );
