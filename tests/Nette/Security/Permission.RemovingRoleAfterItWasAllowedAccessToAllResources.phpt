<?php

/**
 * Test: Nette\Security\Permission Confirm that deleting a role after allowing access to all roles
 * raise undefined index error.
 *
 * @author     David Grudl
 * @package    Nette\Security
 */

use Nette\Security\Permission;


require __DIR__ . '/../bootstrap.php';


$acl = new Permission;
$acl->addRole('test0');
$acl->addRole('test1');
$acl->addRole('test2');
$acl->addResource('Test');

$acl->allow(NULL,'Test','xxx');

// error test
$acl->removeRole('test0');

// Check after fix
Assert::false( $acl->hasRole('test0') );
