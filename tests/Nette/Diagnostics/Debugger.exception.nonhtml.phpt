<?php

/**
 * Test: Nette\Diagnostics\Debugger exception in non-HTML mode.
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
Debugger::$productionMode = FALSE;
header('Content-Type: text/plain');

Debugger::enable();

register_shutdown_function(function(){
	Assert::same('', ob_get_clean());
	echo 'OK!';
});
ob_start();


throw new Exception('The my exception', 123);
