<?php

/**
 * Test: Nette\Security\Permission Ensures that removal of all Resources works.
 *
 * @author     David Grudl
 * @package    Nette\Security
 */

use Nette\Security\Permission;


require __DIR__ . '/../bootstrap.php';


$acl = new Permission;
$acl->addResource('area');
$acl->removeAllResources();
Assert::false( $acl->hasResource('area') );
