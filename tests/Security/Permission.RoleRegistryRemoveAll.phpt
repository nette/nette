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
T::dump( $acl->hasRole('guest') );



__halt_compiler() ?>

------EXPECT------
bool(FALSE)
