<?php

/**
 * Test: Nette\Security\Permission Ensures that the default rule obeys its assertion.
 *
 * @author     David Grudl
 * @package    Nette\Security
 */

use Nette\Security\Permission;


require __DIR__ . '/../bootstrap.php';


function falseAssertion()
{
	return FALSE;
}


$acl = new Permission;
$acl->deny(NULL, NULL, NULL, 'falseAssertion');
Assert::true( $acl->isAllowed(NULL, NULL, 'somePrivilege') );
