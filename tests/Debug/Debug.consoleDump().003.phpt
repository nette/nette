<?php

/**
 * Test: Nette\Debug::consoleDump() in production mode.
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

Debug::consoleDump('value');



__halt_compiler() ?>

------EXPECT------
