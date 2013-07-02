<?php

/**
 * Test: Nette\Security\Permission Ensures that assertions on privileges work properly for a particular Role.
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

function trueAssertion()
{
	return TRUE;
}


$acl = new Permission;
$acl->addRole('guest');
$acl->allow('guest', NULL, 'somePrivilege', 'trueAssertion');
Assert::true( $acl->isAllowed('guest', NULL, 'somePrivilege') );
$acl->allow('guest', NULL, 'somePrivilege', 'falseAssertion');
Assert::false( $acl->isAllowed('guest', NULL, 'somePrivilege') );
