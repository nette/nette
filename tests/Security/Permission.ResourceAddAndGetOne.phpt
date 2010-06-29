<?php

/**
 * Test: Nette\Security\Permission Ensures that basic addition and retrieval of a single Resource works.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Security
 * @subpackage UnitTests
 */

use Nette\Security\Permission;



require __DIR__ . '/../initialize.php';



$acl = new Permission;
T::dump( $acl->hasResource('area') );
$acl->addResource('area');
T::dump( $acl->hasResource('area') );
$acl->removeResource('area');
T::dump( $acl->hasResource('area') );



__halt_compiler() ?>

------EXPECT------
bool(FALSE)

bool(TRUE)

bool(FALSE)
