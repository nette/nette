<?php

/**
 * Test: Nette\Security\Permission Ensures that basic addition and retrieval of a single Role works.
 *
 * @author     David Grudl
 * @package    Nette\Security
 */

use Nette\Security\Permission;



require __DIR__ . '/../bootstrap.php';



$acl = new Permission;
Assert::false( $acl->hasRole('user') );

$acl->addRole('user');
Assert::true( $acl->hasRole('user') );

$acl->removeRole('user');
Assert::false( $acl->hasRole('user') );
