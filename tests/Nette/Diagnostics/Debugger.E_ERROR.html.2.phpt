<?php

/**
 * Test: Nette\Diagnostics\Debugger E_ERROR in HTML.
 *
 * @author     Filip Procházka
 * @package    Nette\Diagnostics
 */

use Nette\Diagnostics\Debugger;



require __DIR__ . '/../bootstrap.php';



Debugger::$productionMode = FALSE;
header('Content-Type: text/html');

Debugger::enable();

Debugger::$onFatalError[] = function() {
	Assert::match(file_get_contents(__DIR__ . (extension_loaded('xdebug') ? '/Debugger.E_ERROR.html.2.xdebug.expect' : '/Debugger.E_ERROR.html.2.expect')), ob_get_clean());
	die(0);
};
ob_start();




function first($arg1, $arg2)
{
	$arr = new Nette\ArrayHash;
	$arr->delete();
}


first(10, 'any string');
