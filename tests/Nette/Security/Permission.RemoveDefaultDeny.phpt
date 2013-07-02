<?php

/**
 * Test: Nette\Security\Permission Ensures that removing the default deny rule results in default deny rule.
 *
 * @author     David Grudl
 * @package    Nette\Security
 */

use Nette\Security\Permission;


require __DIR__ . '/../bootstrap.php';


$acl = new Permission;
Assert::false( $acl->isAllowed() );
$acl->removeDeny();
Assert::false( $acl->isAllowed() );
