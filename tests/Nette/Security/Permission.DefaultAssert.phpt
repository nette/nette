<?php

/**
 * Test: Nette\Security\Permission Ensures that the default rule obeys its assertion.
 */

use Nette\Security\Permission;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


function falseAssertion()
{
	return FALSE;
}


$acl = new Permission;
$acl->deny(NULL, NULL, NULL, 'falseAssertion');
Assert::true($acl->isAllowed(NULL, NULL, 'somePrivilege'));
