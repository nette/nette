<?php

/**
 * Test: Nette\Security\Permission Ensures that an exception is thrown when a non-existent Resource is specified for removal.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Security
 * @subpackage UnitTests
 */

use Nette\Security\Permission;



require __DIR__ . '/../initialize.php';



$acl = new Permission;
try {
	$acl->removeResource('nonexistent');
} catch (InvalidStateException $e) {
	T::dump( $e );
}


__halt_compiler() ?>

------EXPECT------
Exception InvalidStateException: Resource 'nonexistent' does not exist.
