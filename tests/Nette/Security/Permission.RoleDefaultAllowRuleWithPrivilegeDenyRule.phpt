<?php

/**
 * Test: Nette\Security\Permission Ensures that for a particular Role, a deny rule on a specific privilege is honored before an allow
 * rule on the entire ACL.
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
$acl->deny();
$acl->allow('staff');
$acl->deny('staff', NULL, array('privilege1', 'privilege2'));
Assert::false( $acl->isAllowed($identity, NULL, 'privilege1') );
