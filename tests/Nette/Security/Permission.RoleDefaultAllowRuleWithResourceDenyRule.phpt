<?php

/**
 * Test: Nette\Security\Permission Ensures that for a particular Role, a deny rule on a specific Resource is honored before an allow rule
* on the entire ACL.
 *
 * @author     David Grudl
 * @author     Jachym Tousek
 * @package    Nette\Security
 */

use Nette\Security\Permission,
	Nette\Security\Identity;



require __DIR__ . '/../bootstrap.php';



$identity = new Identity(1, array('staff'));
$acl = new Permission;
$acl->addRole('user');
$acl->addRole('staff', 'user');
$acl->addResource('area1');
$acl->addResource('area2');
$acl->deny();
$acl->allow('staff');
$acl->deny('staff', array('area1', 'area2'));
Assert::false( $acl->isAllowed($identity, 'area1') );
