<?php

/**
 * Test: Nette\Security\Permission Ensures that basic addition and retrieval of a single Role works.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Security
 * @subpackage UnitTests
 */

use Nette\Security\Permission;



require __DIR__ . '/../initialize.php';



$acl = new Permission;
T::dump( $acl->hasRole('guest') );
$acl->addRole('guest');
T::dump( $acl->hasRole('guest') );
$acl->removeRole('guest');
T::dump( $acl->hasRole('guest') );



__halt_compiler() ?>

------EXPECT------
bool(FALSE)

bool(TRUE)

bool(FALSE)
