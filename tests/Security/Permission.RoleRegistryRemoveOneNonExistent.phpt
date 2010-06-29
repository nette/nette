<?php

/**
 * Test: Nette\Security\Permission Ensures that an exception is thrown when a non-existent Role is specified for removal.
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
	$acl->removeRole('nonexistent');
} catch (InvalidStateException $e) {
	T::dump( $e );
}



__halt_compiler() ?>

------EXPECT------
Exception InvalidStateException: Role 'nonexistent' does not exist.
