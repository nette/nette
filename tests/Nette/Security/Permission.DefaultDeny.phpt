<?php

/**
 * Test: Nette\Security\Permission Ensures that by default denies access to everything by all.
 */

use Nette\Security\Permission;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$acl = new Permission;
Assert::false($acl->isAllowed());
Assert::false($acl->isAllowed(NULL, NULL, 'somePrivilege'));

$acl->addRole('guest');
Assert::false($acl->isAllowed('guest'));
Assert::false($acl->isAllowed('guest', NULL, 'somePrivilege'));
