<?php

/**
 * Test: Nette\Security\Permission Ensures that assertions on privileges work properly for a particular Role.
 *
 * @author     David Grudl
 * @author     Jachym Tousek
 * @package    Nette\Security
 */

use Nette\Security\Permission,
	Nette\Security\Identity;



require __DIR__ . '/../bootstrap.php';


function falseAssertion()
{
	return FALSE;
}

function trueAssertion()
{
	return TRUE;
}


$identity = new Identity(1, array('user'));
$acl = new Permission;
$acl->addRole('user');
$acl->allow('user', NULL, 'somePrivilege', 'trueAssertion');
Assert::true( $acl->isAllowed($identity, NULL, 'somePrivilege') );
$acl->allow('user', NULL, 'somePrivilege', 'falseAssertion');
Assert::false( $acl->isAllowed($identity, NULL, 'somePrivilege') );
