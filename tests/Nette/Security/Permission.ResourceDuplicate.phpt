<?php

/**
 * Test: Nette\Security\Permission Ensures that the same Resource cannot be added more than once.
 *
 * @author     David Grudl
 */

use Nette\Security\Permission,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::exception(function() {
	$acl = new Permission;
	$acl->addResource('area');
	$acl->addResource('area');
}, 'Nette\InvalidStateException', "Resource 'area' already exists in the list.");
