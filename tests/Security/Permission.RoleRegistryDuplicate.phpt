<?php

/**
 * Test: Nette\Security\Permission Ensures that the same Role cannot be registered more than once to the registry.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Security
 * @subpackage UnitTests
 */

/*use Nette\Security\Permission;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



$acl = new Permission;
try {
	$acl->addRole('guest');
	$acl->addRole('guest');
} catch (InvalidStateException $e) {
	dump( $e );
}



__halt_compiler() ?>

------EXPECT------
Exception InvalidStateException: Role 'guest' already exists in the list.
