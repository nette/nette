<?php

/**
 * Test: Nette\Security\Permission Tests basic Role multiple inheritance.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Security
 * @subpackage UnitTests
 */

/*use Nette\Security\Permission;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



$acl = new Permission;
$acl->addRole('parent1');
$acl->addRole('parent2');
$acl->addRole('child', array('parent1', 'parent2'));

dump( $acl->getRoleParents('child') );

Assert::true( $acl->roleInheritsFrom('child', 'parent1') );
Assert::true( $acl->roleInheritsFrom('child', 'parent2') );

$acl->removeRole('parent1');
dump( $acl->getRoleParents('child') );
Assert::true( $acl->roleInheritsFrom('child', 'parent2') );



__halt_compiler();

------EXPECT------
array(2) {
	0 => string(7) "parent1"
	1 => string(7) "parent2"
}

array(1) {
	0 => string(7) "parent2"
}
