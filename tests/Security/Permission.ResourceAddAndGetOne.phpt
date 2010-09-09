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
Assert::false( $acl->hasResource('area') );

$acl->addResource('area');
Assert::true( $acl->hasResource('area') );

$acl->removeResource('area');
Assert::false( $acl->hasResource('area') );
