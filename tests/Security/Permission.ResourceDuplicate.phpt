<?php

/**
 * Test: Nette\Security\Permission Ensures that the same Resource cannot be added more than once.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Security
 * @subpackage UnitTests
 */

use Nette\Security\Permission;



require __DIR__ . '/../initialize.php';



try {
	$acl = new Permission;
	$acl->addResource('area');
	$acl->addResource('area');
} catch (InvalidStateException $e) {
	T::dump( $e );
}



__halt_compiler() ?>

------EXPECT------
Exception InvalidStateException: Resource 'area' already exists in the list.
