<?php

/**
 * Test: Nette\Security\Permission Ensures that a privilege allowed for a particular Role upon all Resources works properly.
 *
 * @author     David Grudl
 */

use Nette\Security\Permission,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$acl = new Permission;
$acl->addRole('guest');
$acl->allow('guest', NULL, 'somePrivilege');
Assert::true( $acl->isAllowed('guest', NULL, 'somePrivilege') );
