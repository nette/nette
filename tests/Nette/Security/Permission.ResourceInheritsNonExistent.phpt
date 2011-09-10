<?php

/**
 * Test: Nette\Security\Permission Ensures that an exception is thrown when a non-existent Resource is specified to each parameter of inherits().
 *
 * @author     David Grudl
 * @package    Nette\Security
 * @subpackage UnitTests
 */

use Nette\Security\Permission;



require __DIR__ . '/../bootstrap.php';



$acl = new Permission;
$acl->addResource('area');
Assert::throws(function() use ($acl) {
	$acl->resourceInheritsFrom('nonexistent', 'area');
}, 'Nette\InvalidStateException', "Resource 'nonexistent' does not exist.");

Assert::throws(function() use ($acl) {
	$acl->resourceInheritsFrom('area', 'nonexistent');
}, 'Nette\InvalidStateException', "Resource 'nonexistent' does not exist.");
