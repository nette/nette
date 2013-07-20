<?php

/**
 * Test: Nette\Diagnostics\Debugger notices and warnings with $strictMode in console.
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
Debugger::$productionMode = FALSE;

Debugger::$strictMode = TRUE;
Debugger::enable();

Debugger::$onFatalError[] = function() {
	Assert::match("exception 'Nette\\FatalErrorException' with message 'Undefined variable: x' in %a%
Stack trace:
#0 %a%: %a%Debugger::_errorHandler(8, '%a%', '%a%', %a%, Array)
#1 %a%: third(Array)
#2 %a%: second(true, false)
#3 %a%: first(10, 'any string')
#4 {main}
(stored in %a%)
", ob_get_clean());
	echo 'OK!';
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
	$x++;
}


first(10, 'any string');
