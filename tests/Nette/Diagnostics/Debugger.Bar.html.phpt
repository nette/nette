<?php

/**
 * Test: Nette\Diagnostics\Debugger Bar in HTML.
 *
 * @author     David Grudl
 * @package    Nette\Diagnostics
 * @outputMatch %A%<!-- Nette Debug Bar -->%A%
 */

use Nette\Diagnostics\Debugger;


require __DIR__ . '/../bootstrap.php';

if (PHP_SAPI === 'cli') {
	Tester\Environment::skip('Debugger Bar is not rendered in CLI mode');
}


Debugger::$productionMode = FALSE;
header('Content-Type: text/html');

Debugger::enable();
