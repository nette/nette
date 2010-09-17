<?php

/**
 * Test: Nette\Web\Session::regenerateId()
 *
 * @author     David Grudl
 * @package    Nette\Web
 * @subpackage UnitTests
 */

use Nette\Web\Session;



require __DIR__ . '/../bootstrap.php';



$session = new Session;
$session->start();
$oldId = $session->getId();
$session->regenerateId();
$newId = $session->getId();
Assert::true( $newId != $oldId );
