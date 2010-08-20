<?php

/**
 * Test: Nette\Security\Permission Ensures that an exception is thrown when a non-existent Resource is specified to each parameter of inherits().
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Security
 * @subpackage UnitTests
 */

use Nette\Security\Permission;



require __DIR__ . '/../initialize.php';



$acl = new Permission;
$acl->addResource('area');
try {
	$acl->resourceInheritsFrom('nonexistent', 'area');
	Assert::failed();
} catch (Exception $e) {
	Assert::exception('InvalidStateException', "Resource 'nonexistent' does not exist.", $e );
}

try {
	$acl->resourceInheritsFrom('area', 'nonexistent');
	Assert::failed();
} catch (Exception $e) {
	Assert::exception('InvalidStateException', "Resource 'nonexistent' does not exist.", $e );
}
