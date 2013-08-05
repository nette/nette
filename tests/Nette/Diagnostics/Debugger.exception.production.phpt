<?php

/**
 * Test: Nette\Diagnostics\Debugger exception in production mode.
 *
 * @author     David Grudl
 * @package    Nette\Diagnostics
 * @httpCode   500
 * @exitCode   254
 * @outputMatch %A%<h1>Server Error</h1>%A%
 */

use Nette\Diagnostics\Debugger;


require __DIR__ . '/../bootstrap.php';

if (PHP_SAPI === 'cli') {
	Tester\Environment::skip('Error page is not rendered in CLI mode');
}


Debugger::$productionMode = TRUE;
header('Content-Type: text/html');

Debugger::enable();

throw new Exception('The my exception', 123);
