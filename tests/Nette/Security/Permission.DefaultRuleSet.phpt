<?php

/**
 * Test: Nette\Security\Permission Ensures that ACL-wide rules (all Roles, Resources, and privileges) work properly.
 *
 * @author     David Grudl
 */

use Nette\Security\Permission,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$acl = new Permission;
$acl->allow();
Assert::true( $acl->isAllowed() );
Assert::true( $acl->isAllowed(NULL, NULL, 'somePrivilege') );

$acl->deny();
Assert::false( $acl->isAllowed() );
Assert::false( $acl->isAllowed(NULL, NULL, 'somePrivilege') );
