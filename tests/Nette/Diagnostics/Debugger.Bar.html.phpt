<?php

/**
 * Test: Nette\Diagnostics\Debugger Bar in HTML.
 *
 * @author     David Grudl
 * @outputMatch %A%<!-- Tracy Debug Bar -->%A%
 */

use Nette\Diagnostics\Debugger,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

if (PHP_SAPI === 'cli') {
	Tester\Environment::skip('Debugger Bar is not rendered in CLI mode');
}


Debugger::$productionMode = FALSE;
header('Content-Type: text/html');

Debugger::enable();
