<?php

/**
 * Test: Nette\Security\Permission Ensures that removal of all Roles works.
 *
 * @author     David Grudl
 * @package    Nette\Security
 */

use Nette\Security\Permission;


require __DIR__ . '/../bootstrap.php';


$acl = new Permission;
$acl->addRole('guest');
$acl->removeAllRoles();
Assert::false( $acl->hasRole('guest') );
