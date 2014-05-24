<?php

/**
 * Test: Nette\Security\Permission Ensures that an exception is thrown when a non-existent Role is specified for removal.
 */

use Nette\Security\Permission,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$acl = new Permission;
Assert::exception(function() use ($acl) {
	$acl->removeRole('nonexistent');
}, 'Nette\InvalidStateException', "Role 'nonexistent' does not exist.");
