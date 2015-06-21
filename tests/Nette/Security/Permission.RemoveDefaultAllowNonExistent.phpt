<?php

/**
 * Test: Nette\Security\Permission Ensures that removing non-existent default allow rule does nothing.
 */

use Nette\Security\Permission;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$acl = new Permission;
$acl->removeAllow();
Assert::false($acl->isAllowed());
