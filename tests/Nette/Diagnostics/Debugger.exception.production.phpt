<?php

/**
 * Test: Nette\Diagnostics\Debugger exception in production mode.
 *
 * @author     David Grudl
 * @package    Nette\Diagnostics
 * @exitCode   254
 * @httpCode   500
 * @outputMatch OK!
 */

use Nette\Diagnostics\Debugger;


require __DIR__ . '/../bootstrap.php';


Debugger::$consoleMode = FALSE;
Debugger::$productionMode = TRUE;
header('Content-Type: text/html');

Debugger::enable();

register_shutdown_function(function(){
	Assert::match('%A%<h1>Server Error</h1>%A%', ob_get_clean());
	echo 'OK!';
});
ob_start();


throw new Exception('The my exception', 123);
