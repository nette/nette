<?php

/**
 * Test: Nette\Diagnostics\Debugger eval error in HTML.
 *
 * @author     David Grudl
 * @httpCode   500
 * @exitCode   254
 * @outputMatchFile Debugger.error-in-eval.expect
 */

use Nette\Diagnostics\Debugger,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

if (PHP_SAPI === 'cli') {
	Tester\Environment::skip('Debugger Bluescreen is not rendered in CLI mode');
}


Debugger::$productionMode = FALSE;
header('Content-Type: text/html');

Debugger::enable();

function first($user, $pass)
{
	eval('trigger_error("The my error", E_USER_ERROR);');
}


first('root', 'xxx');
