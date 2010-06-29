<?php

/**
 * Test: Nette\Security\Permission Ensures that removal of a Role results in its rules being removed.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Security
 * @subpackage UnitTests
 */

use Nette\Security\Permission;



require __DIR__ . '/../initialize.php';



$acl = new Permission;
$acl->addRole('guest');
$acl->allow('guest');
Assert::true( $acl->isAllowed('guest') );
$acl->removeRole('guest');
try {
	$acl->isAllowed('guest');
} catch (InvalidStateException $e) {
	T::dump( $e );
}

$acl->addRole('guest');
Assert::false( $acl->isAllowed('guest') );



__halt_compiler() ?>

------EXPECT------
Exception InvalidStateException: Role 'guest' does not exist.
