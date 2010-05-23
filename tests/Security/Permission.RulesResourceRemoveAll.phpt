<?php

/**
 * Test: Nette\Security\Permission Ensures that removal of all Resources results in Resource-specific rules being removed.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Security
 * @subpackage UnitTests
 */

/*use Nette\Security\Permission;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



$acl = new Permission;
$acl->addResource('area');
$acl->allow(NULL, 'area');
Assert::true( $acl->isAllowed(NULL, 'area') );
$acl->removeAllResources();
try {
	$acl->isAllowed(NULL, 'area');
} catch (InvalidStateException $e) {
	dump( $e );
}

$acl->addResource('area');
Assert::false( $acl->isAllowed(NULL, 'area') );



__halt_compiler() ?>

------EXPECT------
Exception InvalidStateException: Resource 'area' does not exist.
