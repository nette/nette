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
try {
	$acl->resourceInheritsFrom('nonexistent', 'area');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('Nette\InvalidStateException', "Resource 'nonexistent' does not exist.", $e );
}

try {
	$acl->resourceInheritsFrom('area', 'nonexistent');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('Nette\InvalidStateException', "Resource 'nonexistent' does not exist.", $e );
}
