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
} catch (InvalidStateException $e) {
	T::dump( $e );
}

try {
	$acl->resourceInheritsFrom('area', 'nonexistent');
} catch (InvalidStateException $e) {
	T::dump( $e );
}



__halt_compiler() ?>

------EXPECT------
Exception InvalidStateException: Resource 'nonexistent' does not exist.

Exception InvalidStateException: Resource 'nonexistent' does not exist.
