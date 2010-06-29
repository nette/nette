<?php

/**
 * Test: Nette\Security\Permission Ensures that a privilege denied for a particular Role upon all Resources works properly.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Security
 * @subpackage UnitTests
 */

use Nette\Security\Permission;



require __DIR__ . '/../initialize.php';



$acl = new Permission;
$acl->addRole('guest');
$acl->allow('guest');
$acl->deny('guest', NULL, 'somePrivilege');
Assert::false( $acl->isAllowed('guest', NULL, 'somePrivilege') );
