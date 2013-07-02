<?php

/**
 * Test: Nette\Security\Permission Ensures that ACL-wide rules (all Resources and privileges) work properly for a particular Role.
 *
 * @author     David Grudl
 * @package    Nette\Security
 */

use Nette\Security\Permission;


require __DIR__ . '/../bootstrap.php';


$acl = new Permission;
$acl->addRole('guest');
$acl->allow('guest');
Assert::true( $acl->isAllowed('guest') );
$acl->deny('guest');
Assert::false( $acl->isAllowed('guest') );
