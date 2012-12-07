<?php

/**
 * Test: Nette\Diagnostics\Debugger E_ERROR in console.
 *
 * @author     David Grudl
 * @package    Nette\Diagnostics
 */

use Nette\Diagnostics\Debugger;



require __DIR__ . '/../bootstrap.php';



Debugger::$productionMode = FALSE;
header('Content-Type: text/plain');

Debugger::enable();

Debugger::$onFatalError[] = function() {
	Assert::match("
Fatal error: Call to undefined function missing_funcion() in %a%
exception 'Nette\FatalErrorException' with message 'Call to undefined function missing_funcion()' in %a%
Stack trace:
#0 %a%/Debugger.E_ERROR.console.phpt(47): third()
#1 %a%/Debugger.E_ERROR.console.phpt(41): second()
#2 %a%/Debugger.E_ERROR.console.phpt(57): first()
#3 {main}
(stored in %a%)
", ob_get_clean());
	die(0);
};
ob_start();


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
