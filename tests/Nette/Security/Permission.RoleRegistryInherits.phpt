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
$acl->addRole('guest');
$acl->addRole('member', 'guest');
$acl->addRole('editor', 'member');
Assert::same( array('guest', 'member', 'editor'), $acl->getRoles() );
Assert::same( array(), $acl->getRoleParents('guest') );
Assert::same( array('guest'), $acl->getRoleParents('member') );
Assert::same( array('member'), $acl->getRoleParents('editor') );


Assert::true( $acl->roleInheritsFrom('member', 'guest', TRUE) );
Assert::true( $acl->roleInheritsFrom('editor', 'member', TRUE) );
Assert::true( $acl->roleInheritsFrom('editor', 'guest') );
Assert::false( $acl->roleInheritsFrom('editor', 'guest', TRUE) );
Assert::false( $acl->roleInheritsFrom('guest', 'member') );
Assert::false( $acl->roleInheritsFrom('member', 'editor') );
Assert::false( $acl->roleInheritsFrom('guest', 'editor') );

$acl->removeRole('member');
Assert::same( array(), $acl->getRoleParents('editor') );
Assert::false( $acl->roleInheritsFrom('editor', 'guest') );
