<?php

/**
 * Test: Nette\Diagnostics\Debugger Bar in non-HTML mode.
 * @outputMatch
 */

use Nette\Diagnostics\Debugger,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Debugger::$productionMode = FALSE;
header('Content-Type: text/plain');

Debugger::enable();
