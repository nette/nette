<?php

/**
 * Test: Nette\Security\Permission Ensures that multiple privileges work properly for a particular Role.
 *
 * @author     David Grudl
 * @package    Nette\Security
 */

use Nette\Security\Permission;


require __DIR__ . '/../bootstrap.php';


$acl = new Permission;
$acl->addRole('guest');
$acl->allow('guest', NULL, array('p1', 'p2', 'p3'));
Assert::true( $acl->isAllowed('guest', NULL, 'p1') );
Assert::true( $acl->isAllowed('guest', NULL, 'p2') );
Assert::true( $acl->isAllowed('guest', NULL, 'p3') );
Assert::false( $acl->isAllowed('guest', NULL, 'p4') );
$acl->deny('guest', NULL, 'p1');
Assert::false( $acl->isAllowed('guest', NULL, 'p1') );
$acl->deny('guest', NULL, array('p2', 'p3'));
Assert::false( $acl->isAllowed('guest', NULL, 'p2') );
Assert::false( $acl->isAllowed('guest', NULL, 'p3') );
