<?php

/**
 * Test: Nette\Security\Permission Ensures that removal of all Roles works.
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
$acl->removeAllRoles();
Assert::false( $acl->hasRole('guest') );
