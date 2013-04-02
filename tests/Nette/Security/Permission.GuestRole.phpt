<?php

/**
 * Test: Nette\Security\Permission Ensures that guest role is automatically added to NULL identity.
 *
 * @author     Jachym Tousek
 * @package    Nette\Security
 */

use Nette\Security\Permission,
	Nette\Security\Identity;



require __DIR__ . '/../bootstrap.php';



$identity = NULL;
$acl = new Permission;
$acl->allow('guest');
Assert::true( $acl->isAllowed($identity) );
