<?php

/**
 * Test: Nette\Security\Permission Ensures that an exception is thrown when a non-existent Role is specified to each parameter of inherits().
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Security
 * @subpackage UnitTests
 */

use Nette\Security\Permission;



require __DIR__ . '/../NetteTest/initialize.php';



$acl = new Permission;
$acl->addRole('guest');
try {
	$acl->roleInheritsFrom('nonexistent', 'guest');
} catch (InvalidStateException $e) {
	dump( $e );
}

try {
	$acl->roleInheritsFrom('guest', 'nonexistent');
} catch (InvalidStateException $e) {
	dump( $e );
}



__halt_compiler() ?>

------EXPECT------
Exception InvalidStateException: Role 'nonexistent' does not exist.

Exception InvalidStateException: Role 'nonexistent' does not exist.
