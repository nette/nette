<?php

/**
 * Test: Nette\Security\Permission Tests basic Role inheritance.
 *
 * @author     David Grudl
 * @package    Nette\Security
 */

use Nette\Security\Permission;



require __DIR__ . '/../bootstrap.php';



$acl = new Permission;
$acl->addRole('user');
$acl->addRole('member', 'user');
$acl->addRole('editor', 'member');
Assert::same( array('user', 'member', 'editor'), $acl->getRoles() );
Assert::same( array(), $acl->getRoleParents('user') );
Assert::same( array('user'), $acl->getRoleParents('member') );
Assert::same( array('member'), $acl->getRoleParents('editor') );


Assert::true( $acl->roleInheritsFrom('member', 'user', TRUE) );
Assert::true( $acl->roleInheritsFrom('editor', 'member', TRUE) );
Assert::true( $acl->roleInheritsFrom('editor', 'user') );
Assert::false( $acl->roleInheritsFrom('editor', 'user', TRUE) );
Assert::false( $acl->roleInheritsFrom('user', 'member') );
Assert::false( $acl->roleInheritsFrom('member', 'editor') );
Assert::false( $acl->roleInheritsFrom('user', 'editor') );

$acl->removeRole('member');
Assert::same( array(), $acl->getRoleParents('editor') );
Assert::false( $acl->roleInheritsFrom('editor', 'user') );
