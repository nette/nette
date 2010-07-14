<?php

/**
 * Test: Nette\Security\Permission Ensures that removal of all Resources works.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Security
 * @subpackage UnitTests
 */

use Nette\Security\Permission;



require __DIR__ . '/../initialize.php';



$acl = new Permission;
$acl->addResource('area');
$acl->removeAllResources();
T::dump( $acl->hasResource('area') );



__halt_compiler() ?>

------EXPECT------
FALSE
