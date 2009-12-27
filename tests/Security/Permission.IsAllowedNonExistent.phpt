<?php

/**
 * Test: Nette\Security\Permission Ensures that an exception is thrown when a non-existent Role and Resource parameters are specified to isAllowed().
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Security
 * @subpackage UnitTests
 */

/*use Nette\Security\Permission;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



try {
	$acl = new Permission;
	$acl->isAllowed('nonexistent');
} catch (InvalidStateException $e) {
	dump( $e );
}

try {
	$acl = new Permission;
	$acl->isAllowed(NULL, 'nonexistent');
} catch (InvalidStateException $e) {
	dump( $e );
}



__halt_compiler();

------EXPECT------
Exception InvalidStateException: Role 'nonexistent' does not exist.

Exception InvalidStateException: Resource 'nonexistent' does not exist.
