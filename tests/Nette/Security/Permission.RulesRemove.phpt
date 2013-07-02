<?php

/**
 * Test: Nette\Security\Permission Ensure that basic rule removal works.
 *
 * @author     David Grudl
 * @package    Nette\Security
 */

use Nette\Security\Permission;


require __DIR__ . '/../bootstrap.php';


$acl = new Permission;
$acl->allow(NULL, NULL, array('privilege1', 'privilege2'));
Assert::false( $acl->isAllowed() );
Assert::true( $acl->isAllowed(NULL, NULL, 'privilege1') );
Assert::true( $acl->isAllowed(NULL, NULL, 'privilege2') );
$acl->removeAllow(NULL, NULL, 'privilege1');
Assert::false( $acl->isAllowed(NULL, NULL, 'privilege1') );
Assert::true( $acl->isAllowed(NULL, NULL, 'privilege2') );
