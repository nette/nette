<?php

/**
 * Test: Nette\Security\Permission Ensures that multiple privileges work properly for a particular Role.
 *
 * @author     David Grudl
 * @author     Jachym Tousek
 * @package    Nette\Security
 */

use Nette\Security\Permission,
	Nette\Security\Identity;



require __DIR__ . '/../bootstrap.php';



$identity = new Identity(1, array('user'));
$acl = new Permission;
$acl->addRole('user');
$acl->allow('user', NULL, array('p1', 'p2', 'p3'));
Assert::true( $acl->isAllowed($identity, NULL, 'p1') );
Assert::true( $acl->isAllowed($identity, NULL, 'p2') );
Assert::true( $acl->isAllowed($identity, NULL, 'p3') );
Assert::false( $acl->isAllowed($identity, NULL, 'p4') );
$acl->deny('user', NULL, 'p1');
Assert::false( $acl->isAllowed($identity, NULL, 'p1') );
$acl->deny('user', NULL, array('p2', 'p3'));
Assert::false( $acl->isAllowed($identity, NULL, 'p2') );
Assert::false( $acl->isAllowed($identity, NULL, 'p3') );
