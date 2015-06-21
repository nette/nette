<?php

/**
 * Test: Nette\Security\Permission Ensures that assertions on privileges work properly.
 */

use Nette\Security\Permission;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


function falseAssertion()
{
	return FALSE;
}

function trueAssertion()
{
	return TRUE;
}


$acl = new Permission;
$acl->allow(NULL, NULL, 'somePrivilege', 'trueAssertion');
Assert::true($acl->isAllowed(NULL, NULL, 'somePrivilege'));

$acl->allow(NULL, NULL, 'somePrivilege', 'falseAssertion');
Assert::false($acl->isAllowed(NULL, NULL, 'somePrivilege'));
