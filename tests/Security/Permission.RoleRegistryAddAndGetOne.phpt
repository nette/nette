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



require __DIR__ . '/../NetteTest/initialize.php';



$acl = new Permission;
dump( $acl->hasRole('guest') );
$acl->addRole('guest');
dump( $acl->hasRole('guest') );
$acl->removeRole('guest');
dump( $acl->hasRole('guest') );



__halt_compiler() ?>

------EXPECT------
bool(FALSE)

bool(TRUE)

bool(FALSE)
