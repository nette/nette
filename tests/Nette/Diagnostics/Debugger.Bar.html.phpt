<?php

/**
 * Test: Nette\Diagnostics\Debugger Bar in HTML.
 *
 * @author     David Grudl
 * @package    Nette\Diagnostics
 */

use Nette\Diagnostics\Debugger;



require __DIR__ . '/../bootstrap.php';



Debugger::$productionMode = FALSE;
header('Content-Type: text/html');

Debugger::enable();

register_shutdown_function(function(){
	Assert::match('%A%<!-- Nette Debug Bar -->%A%', ob_get_clean());
});
ob_start();
