<?php

/**
 * Test: Nette\Security\Permission Ensures that removing non-existent default deny rule does nothing.
 *
 * @author     David Grudl
 */

use Nette\Security\Permission,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$acl = new Permission;
$acl->allow();
$acl->removeDeny();
Assert::true( $acl->isAllowed() );
