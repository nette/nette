<?php

/**
 * Test: Nette\Security\Permission Ensures that a privilege allowed for all Roles upon all Resources works properly.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Security
 * @subpackage UnitTests
 */

/*use Nette\Security\Permission;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



$acl = new Permission;
$acl->allow(NULL, NULL, 'somePrivilege');
Assert::true( $acl->isAllowed(NULL, NULL, 'somePrivilege') );
