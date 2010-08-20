<?php

/**
 * Test: Nette\Security\Permission Ensures that basic addition and retrieval of a single Role works.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Security
 * @subpackage UnitTests
 */

use Nette\Security\Permission;



require __DIR__ . '/../initialize.php';



$acl = new Permission;
Assert::false( $acl->hasRole('guest') );

$acl->addRole('guest');
Assert::true( $acl->hasRole('guest') );

$acl->removeRole('guest');
Assert::false( $acl->hasRole('guest') );
