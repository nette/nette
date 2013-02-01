<?php

/**
 * Test: Nette\Security\Permission Ensures that an exception is thrown when a non-existent Role is specified to each parameter of inherits().
 *
 * @author     David Grudl
 * @package    Nette\Security
 */

use Nette\Security\Permission;



require __DIR__ . '/../bootstrap.php';



$acl = new Permission;
$acl->addRole('user');
Assert::exception(function() use ($acl) {
	$acl->roleInheritsFrom('nonexistent', 'user');
}, 'Nette\InvalidStateException', "Role 'nonexistent' does not exist.");

Assert::exception(function() use ($acl) {
	$acl->roleInheritsFrom('user', 'nonexistent');
}, 'Nette\InvalidStateException', "Role 'nonexistent' does not exist.");
