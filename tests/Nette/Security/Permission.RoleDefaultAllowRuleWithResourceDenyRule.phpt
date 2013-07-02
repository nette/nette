<?php

/**
 * Test: Nette\Security\Permission Ensures that for a particular Role, a deny rule on a specific Resource is honored before an allow rule
* on the entire ACL.
 *
 * @author     David Grudl
 * @package    Nette\Security
 */

use Nette\Security\Permission;


require __DIR__ . '/../bootstrap.php';


$acl = new Permission;
$acl->addRole('guest');
$acl->addRole('staff', 'guest');
$acl->addResource('area1');
$acl->addResource('area2');
$acl->deny();
$acl->allow('staff');
$acl->deny('staff', array('area1', 'area2'));
Assert::false( $acl->isAllowed('staff', 'area1') );
