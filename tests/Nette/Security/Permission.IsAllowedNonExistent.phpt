<?php

/**
 * Test: Nette\Security\Permission Ensures that an exception is thrown when a non-existent Role and Resource parameters are specified to isAllowed().
 *
 * @author     David Grudl
 * @package    Nette\Security
 * @subpackage UnitTests
 */

use Nette\Security\Permission;



require __DIR__ . '/../bootstrap.php';



Assert::throws(function() {
	$acl = new Permission;
	$acl->isAllowed('nonexistent');
}, 'Nette\InvalidStateException', "Role 'nonexistent' does not exist.");

Assert::throws(function() {
	$acl = new Permission;
	$acl->isAllowed(NULL, 'nonexistent');
}, 'Nette\InvalidStateException', "Resource 'nonexistent' does not exist.");
