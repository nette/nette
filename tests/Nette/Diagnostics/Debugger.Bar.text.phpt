<?php

/**
 * Test: Nette\Diagnostics\Debugger Bar in non-HTML mode.
 *
 * @author     David Grudl
 */

use Nette\Diagnostics\Debugger,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Debugger::$consoleMode = FALSE;
Debugger::$productionMode = FALSE;
header('Content-Type: text/plain');

Debugger::enable();

register_shutdown_function(function(){
	Assert::same('', ob_get_clean());
});
ob_start();
