<?php

/**
 * Test: Nette\Debug::enableProfiler() in production mode.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\Debug;



require __DIR__ . '/../initialize.php';



Debug::$consoleMode = FALSE;
Debug::$productionMode = TRUE;

header('Content-Type: text/html');

Debug::enableProfiler();



__halt_compiler() ?>

------EXPECT------
