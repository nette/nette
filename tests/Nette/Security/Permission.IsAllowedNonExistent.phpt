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



try {
	$acl = new Permission;
	$acl->isAllowed('nonexistent');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('Nette\InvalidStateException', "Role 'nonexistent' does not exist.", $e );
}

try {
	$acl = new Permission;
	$acl->isAllowed(NULL, 'nonexistent');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('Nette\InvalidStateException', "Resource 'nonexistent' does not exist.", $e );
}
