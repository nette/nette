<?php

/**
 * Test: Nette\Security\Permission Ensures that the same Resource cannot be added more than once.
 *
 * @author     David Grudl
 * @package    Nette\Security
 * @subpackage UnitTests
 */

use Nette\Security\Permission;



require __DIR__ . '/../bootstrap.php';



Assert::throws(function() {
	$acl = new Permission;
	$acl->addResource('area');
	$acl->addResource('area');
}, 'Nette\InvalidStateException', "Resource 'area' already exists in the list.");
