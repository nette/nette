<?php

/**
 * Test: Nette\Security\Permission Ensures that a privilege denied for a particular Role upon all Resources works properly.
 *
 * @author     David Grudl
 * @author     Jachym Tousek
 * @package    Nette\Security
 */

use Nette\Security\Permission,
	Nette\Security\Identity;



require __DIR__ . '/../bootstrap.php';



$identity = new Identity(1, array('user'));
$acl = new Permission;
$acl->addRole('user');
$acl->allow('user');
$acl->deny('user', NULL, 'somePrivilege');
Assert::false( $acl->isAllowed($identity, NULL, 'somePrivilege') );
