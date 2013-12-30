<?php

/**
 * Test: Nette\Diagnostics\Debugger Bar in production mode.
 *
 * @author     David Grudl
 * @outputMatch
 */

use Nette\Diagnostics\Debugger,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Debugger::$productionMode = TRUE;
header('Content-Type: text/html');

Debugger::enable();
