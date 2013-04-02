<?php

/**
 * Test: Nette\Security\Permission Ensures that authenticated role is automatically added to identity with no roles.
 *
 * @author     Jachym Tousek
 * @package    Nette\Security
 */

use Nette\Security\Permission,
	Nette\Security\Identity;



require __DIR__ . '/../bootstrap.php';



$acl = new Permission;
$acl->allow('authenticated');

$identity = new Identity(1);
Assert::true( $acl->isAllowed($identity) );

$acl->addRole('user');
$identity = new Identity(1, array('user'));
Assert::false( $acl->isAllowed($identity) );
