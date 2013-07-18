<?php

/**
 * Test: Nette\Diagnostics\Debugger Bar in HTML.
 *
 * @author     David Grudl
 * @package    Nette\Diagnostics
 * @output     %A%<!-- Nette Debug Bar -->%A%
 */

use Nette\Diagnostics\Debugger;


require __DIR__ . '/../bootstrap.php';

if (PHP_SAPI === 'cli') {
	Tester\Helpers::skip();
}


Debugger::$productionMode = FALSE;
header('Content-Type: text/html');

Debugger::enable();
