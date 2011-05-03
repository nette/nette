<?php

/**
 * Test: Nette\Http\Session::regenerateId()
 *
 * @author     David Grudl
 * @package    Nette\Http
 * @subpackage UnitTests
 */

use Nette\Http\Session;



require __DIR__ . '/../bootstrap.php';



$session = Nette\Environment::getSession();
$session->start();
$oldId = $session->getId();
$session->regenerateId();
$newId = $session->getId();
Assert::true( $newId != $oldId );
