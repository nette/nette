<?php

/**
 * Test: Nette\Diagnostics\Debugger E_ERROR in production mode.
 *
 * @author     David Grudl
 * @exitCode   254
 * @httpCode   500
 * @outputMatch OK!
 */

use Nette\Diagnostics\Debugger,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Debugger::$consoleMode = FALSE;
Debugger::$productionMode = TRUE;
header('Content-Type: text/html');

Debugger::enable();

Debugger::$onFatalError[] = function() {
	Assert::match('%A%<h1>Server Error</h1>%A%', ob_get_clean());
	echo 'OK!';
};
ob_start();


missing_function();
