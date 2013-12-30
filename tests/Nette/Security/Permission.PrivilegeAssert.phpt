<?php

/**
 * Test: Nette\Security\Permission Ensures that assertions on privileges work properly.
 *
 * @author     David Grudl
 */

use Nette\Security\Permission,
	Tester\Assert;


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
Assert::true( $acl->isAllowed(NULL, NULL, 'somePrivilege') );

$acl->allow(NULL, NULL, 'somePrivilege', 'falseAssertion');
Assert::false( $acl->isAllowed(NULL, NULL, 'somePrivilege') );
