<?php

/**
 * Test: Nette\Security\Permission Ensures that an exception is thrown when a non-existent Resource is specified as a parent upon Resource addition.
 */

use Nette\Security\Permission;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$acl = new Permission;
Assert::exception(function () use ($acl) {
	$acl->addResource('area', 'nonexistent');
}, 'Nette\InvalidStateException', "Resource 'nonexistent' does not exist.");
