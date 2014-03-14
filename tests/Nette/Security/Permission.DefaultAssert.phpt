<?php

/**
 * Test: Nette\Security\Permission Ensures that the default rule obeys its assertion.
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


$acl = new Permission;
$acl->deny(NULL, NULL, NULL, 'falseAssertion');
Assert::true( $acl->isAllowed(NULL, NULL, 'somePrivilege') );
