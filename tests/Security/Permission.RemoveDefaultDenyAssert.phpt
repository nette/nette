<?php

/**
 * Test: Nette\Security\Permission Ensures that removing the default deny rule results in assertion method being removed.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Security
 * @subpackage UnitTests
 */

use Nette\Security\Permission;



require __DIR__ . '/../NetteTest/initialize.php';



function falseAssertion()
{
	return FALSE;
}



$acl = new Permission;
$acl->deny(NULL, NULL, NULL, 'falseAssertion');
Assert::true( $acl->isAllowed() );
$acl->removeDeny();
Assert::false( $acl->isAllowed() );
