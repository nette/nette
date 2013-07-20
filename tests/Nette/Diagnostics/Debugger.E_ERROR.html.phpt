<?php

/**
 * Test: Nette\Diagnostics\Debugger E_ERROR in HTML.
 *
 * @author     David Grudl
 * @package    Nette\Diagnostics
 * @exitCode   254
 * @httpCode   500
 * @outputMatchFile Debugger.E_ERROR.html.expect
 */

use Nette\Diagnostics\Debugger;


require __DIR__ . '/../bootstrap.php';


Debugger::$consoleMode = FALSE;
Debugger::$productionMode = FALSE;
header('Content-Type: text/html');
ob_start(); // fatal error can write to output

Debugger::enable();


function first($arg1, $arg2)
{
	second(TRUE, FALSE);
}


function second($arg1, $arg2)
{
	third(array(1, 2, 3));
}


function third($arg1)
{
	missing_funcion();
}


first(10, 'any string');
