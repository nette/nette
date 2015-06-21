<?php

/**
 * Test: Nette\Security\Permission Ensures that removing the default deny rule results in assertion method being removed.
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
Assert::true($acl->isAllowed());
$acl->removeDeny();
Assert::false($acl->isAllowed());
