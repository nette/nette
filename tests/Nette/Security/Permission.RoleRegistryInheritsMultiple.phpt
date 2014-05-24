<?php

/**
 * Test: Nette\Security\Permission Tests basic Role multiple inheritance.
 */

use Nette\Security\Permission,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$acl = new Permission;
$acl->addRole('parent1');
$acl->addRole('parent2');
$acl->addRole('child', array('parent1', 'parent2'));

Assert::same( array(
	'parent1',
	'parent2',
), $acl->getRoleParents('child') );


Assert::true( $acl->roleInheritsFrom('child', 'parent1') );
Assert::true( $acl->roleInheritsFrom('child', 'parent2') );

$acl->removeRole('parent1');
Assert::same( array('parent2'), $acl->getRoleParents('child') );
Assert::true( $acl->roleInheritsFrom('child', 'parent2') );
