<?php

/**
 * Test: Nette\Security\Permission Ensures that removal of a Resource results in its rules being removed.
 *
 * @author     David Grudl
 * @package    Nette\Security
 */

use Nette\Security\Permission;


require __DIR__ . '/../bootstrap.php';


$acl = new Permission;
$acl->addResource('area');
$acl->allow(NULL, 'area');
Assert::true( $acl->isAllowed(NULL, 'area') );
$acl->removeResource('area');
Assert::exception(function() use ($acl) {
	$acl->isAllowed(NULL, 'area');
}, 'Nette\InvalidStateException', "Resource 'area' does not exist.");

$acl->addResource('area');
Assert::false( $acl->isAllowed(NULL, 'area') );
