<?php

/**
 * Test: Nette\Security\Permission Ensures that the same Role cannot be registered more than once to the registry.
 *
 * @author     David Grudl
 * @package    Nette\Security
 * @subpackage UnitTests
 */

use Nette\Security\Permission;



require __DIR__ . '/../bootstrap.php';



$acl = new Permission;
try {
	$acl->addRole('guest');
	$acl->addRole('guest');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('Nette\InvalidStateException', "Role 'guest' already exists in the list.", $e );
}
