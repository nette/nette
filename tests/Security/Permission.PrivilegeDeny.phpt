<?php

/**
 * Test: Nette\Security\Permission Ensures that a privilege denied for all Roles upon all Resources works properly.
 *
 * @author     David Grudl
 * @package    Nette\Security
 * @subpackage UnitTests
 */

use Nette\Security\Permission;



require __DIR__ . '/../initialize.php';



$acl = new Permission;
$acl->allow();
$acl->deny(NULL, NULL, 'somePrivilege');
Assert::false( $acl->isAllowed(NULL, NULL, 'somePrivilege') );
