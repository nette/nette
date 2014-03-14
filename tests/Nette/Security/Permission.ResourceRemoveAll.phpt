<?php

/**
 * Test: Nette\Security\Permission Ensures that removal of all Resources works.
 *
 * @author     David Grudl
 */

use Nette\Security\Permission,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$acl = new Permission;
$acl->addResource('area');
$acl->removeAllResources();
Assert::false( $acl->hasResource('area') );
