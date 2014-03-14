<?php

/**
 * Test: Nette\Security\Permission Ensures that a privilege allowed for all Roles upon all Resources works properly.
 *
 * @author     David Grudl
 */

use Nette\Security\Permission,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$acl = new Permission;
$acl->allow(NULL, NULL, 'somePrivilege');
Assert::true( $acl->isAllowed(NULL, NULL, 'somePrivilege') );
