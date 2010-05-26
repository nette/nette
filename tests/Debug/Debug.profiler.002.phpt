<?php

/**
 * Test: Nette\Debug::enableProfiler() in non-HTML mode.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\Debug;



require __DIR__ . '/../NetteTest/initialize.php';



Debug::$consoleMode = FALSE;
Debug::$productionMode = FALSE;

header('Content-Type: text/plain');

Debug::enableProfiler();



__halt_compiler() ?>

------EXPECT------
