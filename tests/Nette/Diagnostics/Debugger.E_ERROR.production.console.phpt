<?php

/**
 * Test: Nette\Diagnostics\Debugger E_ERROR in production & console mode.
 *
 * @author     David Grudl
 * @exitCode   255
 * @httpCode   500
 * @outputMatch ERROR:%A%
 */

use Nette\Diagnostics\Debugger,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Debugger::$productionMode = TRUE;
header('Content-Type: text/plain');

Debugger::enable();

missing_function();
