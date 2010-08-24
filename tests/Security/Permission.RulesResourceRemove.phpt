<?php

/**
 * Test: Nette\Security\Permission Ensures that removal of a Resource results in its rules being removed.
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
$acl->allow(NULL, 'area');
Assert::true( $acl->isAllowed(NULL, 'area') );
$acl->removeResource('area');
try {
	$acl->isAllowed(NULL, 'area');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidStateException', "Resource 'area' does not exist.", $e );
}

$acl->addResource('area');
Assert::false( $acl->isAllowed(NULL, 'area') );
