<?php

/**
 * Test: Nette\Security\Permission Tests basic Resource inheritance.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Security
 * @subpackage UnitTests
 */

use Nette\Security\Permission;



require __DIR__ . '/../initialize.php';



$acl = new Permission;
$acl->addResource('city');
$acl->addResource('building', 'city');
$acl->addResource('room', 'building');

Assert::true( $acl->resourceInheritsFrom('building', 'city', TRUE) );
Assert::true( $acl->resourceInheritsFrom('room', 'building', TRUE) );
Assert::true( $acl->resourceInheritsFrom('room', 'city') );
Assert::false( $acl->resourceInheritsFrom('room', 'city', TRUE) );
Assert::false( $acl->resourceInheritsFrom('city', 'building') );
Assert::false( $acl->resourceInheritsFrom('building', 'room') );
Assert::false( $acl->resourceInheritsFrom('city', 'room') );

$acl->removeResource('building');
Assert::false( $acl->hasResource('room') );
