<?php

/**
 * Test: Nette\Security\Permission Tests basic Role inheritance.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Security
 * @subpackage UnitTests
 */

/*use Nette\Security\Permission;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



$acl = new Permission;
$acl->addRole('guest');
$acl->addRole('member', 'guest');
$acl->addRole('editor', 'member');
dump( $acl->getRoleParents('guest') );
dump( $acl->getRoleParents('member') );
dump( $acl->getRoleParents('editor') );

Assert::true( $acl->roleInheritsFrom('member', 'guest', TRUE) );
Assert::true( $acl->roleInheritsFrom('editor', 'member', TRUE) );
Assert::true( $acl->roleInheritsFrom('editor', 'guest') );
Assert::false( $acl->roleInheritsFrom('editor', 'guest', TRUE) );
Assert::false( $acl->roleInheritsFrom('guest', 'member') );
Assert::false( $acl->roleInheritsFrom('member', 'editor') );
Assert::false( $acl->roleInheritsFrom('guest', 'editor') );

$acl->removeRole('member');
dump( $acl->getRoleParents('editor') );
Assert::false( $acl->roleInheritsFrom('editor', 'guest') );



__halt_compiler() ?>

------EXPECT------
array(0)

array(1) {
	0 => string(5) "guest"
}

array(1) {
	0 => string(6) "member"
}

array(0)
