<?php

/**
 * Test: Nette\Security\Permission Ensures that basic addition and retrieval of a single Role works.
 */

use Nette\Security\Permission,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$acl = new Permission;
Assert::false( $acl->hasRole('guest') );

$acl->addRole('guest');
Assert::true( $acl->hasRole('guest') );

$acl->removeRole('guest');
Assert::false( $acl->hasRole('guest') );
