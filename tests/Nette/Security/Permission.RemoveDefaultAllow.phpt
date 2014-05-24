<?php

/**
 * Test: Nette\Security\Permission Ensures that removing the default allow rule results in default deny rule being assigned.
 */

use Nette\Security\Permission,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$acl = new Permission;
$acl->allow();
Assert::true( $acl->isAllowed() );
$acl->removeAllow();
Assert::false( $acl->isAllowed() );
