<?php

/**
 * Test: Nette\Diagnostics\Debugger Bar in production mode.
 *
 * @author     David Grudl
 * @package    Nette\Diagnostics
 * @outputMatch
 */

use Nette\Diagnostics\Debugger,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Debugger::$productionMode = TRUE;
header('Content-Type: text/html');

Debugger::enable();
