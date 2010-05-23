<?php

/**
 * Test: Nette\Security\Permission Ensures that basic addition and retrieval of a single Resource works.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Security
 * @subpackage UnitTests
 */

/*use Nette\Security\Permission;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



$acl = new Permission;
dump( $acl->hasResource('area') );
$acl->addResource('area');
dump( $acl->hasResource('area') );
$acl->removeResource('area');
dump( $acl->hasResource('area') );



__halt_compiler() ?>

------EXPECT------
bool(FALSE)

bool(TRUE)

bool(FALSE)
