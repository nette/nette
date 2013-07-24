<?php

/**
 * Test: Nette\Security\Permission Ensures that an exception is thrown when a non-existent Role is specified as a parent upon Role addition.
 *
 * @author     David Grudl
 * @package    Nette\Security
 */

use Nette\Security\Permission;


require __DIR__ . '/../bootstrap.php';


$acl = new Permission;
Assert::exception(function() use ($acl) {
	$acl->addRole('guest', 'nonexistent');
}, 'Nette\InvalidStateException', "Role 'nonexistent' does not exist.");
