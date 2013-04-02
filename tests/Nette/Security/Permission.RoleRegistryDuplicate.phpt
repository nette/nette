<?php

/**
 * Test: Nette\Security\Permission Ensures that the same Role cannot be registered more than once to the registry.
 *
 * @author     David Grudl
 * @package    Nette\Security
 */

use Nette\Security\Permission;



require __DIR__ . '/../bootstrap.php';



$acl = new Permission;
Assert::exception(function() use ($acl) {
	$acl->addRole('user');
	$acl->addRole('user');
}, 'Nette\InvalidStateException', "Role 'user' already exists in the list.");
