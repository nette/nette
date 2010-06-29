<?php

/**
 * Test: Nette\Security\Permission Ensures that ACL-wide rules (all Roles, Resources, and privileges) work properly.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Security
 * @subpackage UnitTests
 */

use Nette\Security\Permission;



require __DIR__ . '/../initialize.php';



$acl = new Permission;
$acl->allow();
Assert::true( $acl->isAllowed() );
Assert::true( $acl->isAllowed(NULL, NULL, 'somePrivilege') );

$acl->deny();
Assert::false( $acl->isAllowed() );
Assert::false( $acl->isAllowed(NULL, NULL, 'somePrivilege') );
