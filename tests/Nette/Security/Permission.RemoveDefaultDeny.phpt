<?php

/**
 * Test: Nette\Security\Permission Ensures that removing the default deny rule results in default deny rule.
 */

use Nette\Security\Permission;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$acl = new Permission;
Assert::false($acl->isAllowed());
$acl->removeDeny();
Assert::false($acl->isAllowed());
