<?php

/**
 * Test: Nette\Security\Permission Ensures that removing non-existent default allow rule does nothing.
 *
 * @author     David Grudl
 * @package    Nette\Security
 * @subpackage UnitTests
 */

use Nette\Security\Permission;



require __DIR__ . '/../initialize.php';



$acl = new Permission;
$acl->removeAllow();
Assert::false( $acl->isAllowed() );
