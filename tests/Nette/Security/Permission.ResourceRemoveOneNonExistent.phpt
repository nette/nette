<?php

/**
 * Test: Nette\Security\Permission Ensures that an exception is thrown when a non-existent Resource is specified for removal.
 *
 * @author     David Grudl
 * @package    Nette\Security
 * @subpackage UnitTests
 */

use Nette\Security\Permission;



require __DIR__ . '/../bootstrap.php';



$acl = new Permission;
Assert::throws(function() use ($acl) {
	$acl->removeResource('nonexistent');
}, 'Nette\InvalidStateException', "Resource 'nonexistent' does not exist.");
