<?php

/**
 * Test: Nette\Security\Permission Tests basic Resource inheritance.
 *
 * @author     David Grudl
 * @package    Nette\Security
 */

use Nette\Security\Permission;


require __DIR__ . '/../bootstrap.php';


$acl = new Permission;
$acl->addResource('city');
$acl->addResource('building', 'city');
$acl->addResource('room', 'building');

Assert::same( array('city', 'building', 'room'), $acl->getResources() );
Assert::true( $acl->resourceInheritsFrom('building', 'city', TRUE) );
Assert::true( $acl->resourceInheritsFrom('room', 'building', TRUE) );
Assert::true( $acl->resourceInheritsFrom('room', 'city') );
Assert::false( $acl->resourceInheritsFrom('room', 'city', TRUE) );
Assert::false( $acl->resourceInheritsFrom('city', 'building') );
Assert::false( $acl->resourceInheritsFrom('building', 'room') );
Assert::false( $acl->resourceInheritsFrom('city', 'room') );

$acl->removeResource('building');
Assert::false( $acl->hasResource('room') );
