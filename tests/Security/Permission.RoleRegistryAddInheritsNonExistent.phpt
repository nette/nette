<?php

/**
 * Test: Nette\Security\Permission Ensures that an exception is thrown when a non-existent Role is specified as a parent upon Role addition.
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
	$acl->addRole('guest', 'nonexistent');
} catch (InvalidStateException $e) {
	T::dump( $e );
}



__halt_compiler() ?>

------EXPECT------
Exception InvalidStateException: Role 'nonexistent' does not exist.
