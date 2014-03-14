<?php

/**
 * Test: Nette\Security\Permission Ensures that a privilege denied for all Roles upon all Resources works properly.
 *
 * @author     David Grudl
 */

use Nette\Security\Permission,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$acl = new Permission;
$acl->allow();
$acl->deny(NULL, NULL, 'somePrivilege');
Assert::false( $acl->isAllowed(NULL, NULL, 'somePrivilege') );
