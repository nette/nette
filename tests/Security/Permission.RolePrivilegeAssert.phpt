<?php

/**
 * Test: Nette\Security\Permission Ensures that assertions on privileges work properly for a particular Role.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Security
 * @subpackage UnitTests
 */

use Nette\Security\Permission;



require __DIR__ . '/../NetteTest/initialize.php';

require __DIR__ . '/MockAssertion.inc';



$acl = new Permission;
$acl->addRole('guest');
$acl->allow('guest', NULL, 'somePrivilege', new MockAssertion(TRUE));
Assert::true( $acl->isAllowed('guest', NULL, 'somePrivilege') );
$acl->allow('guest', NULL, 'somePrivilege', new MockAssertion(FALSE));
Assert::false( $acl->isAllowed('guest', NULL, 'somePrivilege') );
