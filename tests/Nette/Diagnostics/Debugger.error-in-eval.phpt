<?php

/**
 * Test: Nette\Diagnostics\Debugger eval error in HTML.
 *
 * @author     David Grudl
 * @package    Nette\Diagnostics
 * @httpCode   500
 * @exitCode   254
 * @outputMatchFile Debugger.error-in-eval.expect
 */

use Nette\Diagnostics\Debugger;


require __DIR__ . '/../bootstrap.php';

if (PHP_SAPI === 'cli') {
	Tester\Environment::skip();
}


Debugger::$productionMode = FALSE;
header('Content-Type: text/html');

Debugger::enable();

function first($user, $pass)
{
	eval('trigger_error("The my error", E_USER_ERROR);');
}


first('root', 'xxx');
