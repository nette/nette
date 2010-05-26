<?php

/**
 * Test: Nette\Security\Permission Ensures that removal of all Roles results in Role-specific rules being removed.
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
$acl->allow('guest');
Assert::true( $acl->isAllowed('guest') );
$acl->removeAllRoles();
try {
	$acl->isAllowed('guest');
} catch (InvalidStateException $e) {
	dump( $e );
}

$acl->addRole('guest');
Assert::false( $acl->isAllowed('guest') );



__halt_compiler() ?>

------EXPECT------
Exception InvalidStateException: Role 'guest' does not exist.
