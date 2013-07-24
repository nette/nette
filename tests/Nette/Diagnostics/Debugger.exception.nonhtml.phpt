<?php

/**
 * Test: Nette\Diagnostics\Debugger exception in non-HTML.
 *
 * @author     David Grudl
 * @package    Nette\Diagnostics
 * @exitCode   254
 * @httpCode   500
 * @outputMatchFile Debugger.exception.nonhtml.expect
 */

use Nette\Diagnostics\Debugger;


require __DIR__ . '/../bootstrap.php';


Debugger::$productionMode = FALSE;
header('Content-Type: text/plain');

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
	throw new Exception('The my exception', 123);
}


first(10, 'any string');
