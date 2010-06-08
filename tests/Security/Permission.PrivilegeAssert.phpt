<?php

/**
 * Test: Nette\Security\Permission Ensures that assertions on privileges work properly.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Security
 * @subpackage UnitTests
 */

use Nette\Security\Permission;



require __DIR__ . '/../NetteTest/initialize.php';



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
