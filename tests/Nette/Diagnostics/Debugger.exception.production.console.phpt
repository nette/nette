<?php

/**
 * Test: Nette\Diagnostics\Debugger exception in production & console mode.
 *
 * @author     David Grudl
 * @package    Nette\Diagnostics
 * @exitCode   254
 * @httpCode   500
 * @outputMatch OK!
 */

use Nette\Diagnostics\Debugger;


require __DIR__ . '/../bootstrap.php';


Debugger::$consoleMode = TRUE;
Debugger::$productionMode = TRUE;

Debugger::enable();

register_shutdown_function(function(){
	Assert::match('ERROR:%A%', ob_get_clean());
	echo 'OK!';
});
ob_start();


throw new Exception('The my exception', 123);
