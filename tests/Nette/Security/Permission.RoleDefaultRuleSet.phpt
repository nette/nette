<?php

/**
 * Test: Nette\Security\Permission Ensures that ACL-wide rules (all Resources and privileges) work properly for a particular Role.
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
Assert::true( $acl->isAllowed($identity) );
$acl->deny('user');
Assert::false( $acl->isAllowed($identity) );
