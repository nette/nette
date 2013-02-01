<?php

/**
 * Test: Nette\Security\Permission Ensures that an exception is thrown when a non-existent Role and Resource parameters are specified to isAllowed().
 *
 * @author     David Grudl
 * @author     Jachym Tousek
 * @package    Nette\Security
 */

use Nette\Security\Permission,
	Nette\Security\Identity;



require __DIR__ . '/../bootstrap.php';



Assert::exception(function() {
    $identity = new Identity(1, array('nonexistent'));
	$acl = new Permission;
	$acl->isAllowed($identity);
}, 'Nette\InvalidStateException', "Role 'nonexistent' does not exist.");

Assert::exception(function() {
	$acl = new Permission;
	$acl->isAllowed(NULL, 'nonexistent');
}, 'Nette\InvalidStateException', "Resource 'nonexistent' does not exist.");
