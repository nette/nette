<?php

/**
 * Test: Nette\Security\Permission Ensures that the default rule obeys its assertion.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Security
 * @subpackage UnitTests
 */

use Nette\Security\Permission;



require __DIR__ . '/../initialize.php';



function falseAssertion()
{
	return FALSE;
}



$acl = new Permission;
$acl->deny(NULL, NULL, NULL, 'falseAssertion');
Assert::true( $acl->isAllowed(NULL, NULL, 'somePrivilege') );
