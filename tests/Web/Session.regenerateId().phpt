<?php

/**
 * Test: Nette\Web\Session::regenerateId()
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Web
 * @subpackage UnitTests
 */

use Nette\Web\Session;



require __DIR__ . '/../initialize.php';



$session = new Session;
$session->start();
$oldId = $session->getId();
$session->regenerateId();
$newId = $session->getId();
T::dump( $newId != $oldId );



__halt_compiler() ?>

------EXPECT------
TRUE
