<?php

/**
 * Test: Nette\Security\Permission Ensures that by default denies access to everything by all.
 *
 * @author     David Grudl
 * @author     Jachym Tousek
 * @package    Nette\Security
 */

use Nette\Security\Permission,
	Nette\Security\Identity;



require __DIR__ . '/../bootstrap.php';



$acl = new Permission;
Assert::false( $acl->isAllowed() );
Assert::false( $acl->isAllowed(NULL, NULL, 'somePrivilege') );

$identity = new Identity(1, array('user'));
$acl->addRole('user');
Assert::false( $acl->isAllowed($identity) );
Assert::false( $acl->isAllowed($identity, NULL, 'somePrivilege') );
