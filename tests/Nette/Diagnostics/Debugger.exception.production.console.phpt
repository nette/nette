<?php

/**
 * Test: Nette\Diagnostics\Debugger exception in production & console mode.
 *
 * @author     David Grudl
 * @package    Nette\Diagnostics
 * @exitCode   254
 * @httpCode   500
 * @outputMatch ERROR:%A%
 */

use Nette\Diagnostics\Debugger;


require __DIR__ . '/../bootstrap.php';


Debugger::$productionMode = TRUE;
header('Content-Type: text/plain');

Debugger::enable();

throw new Exception('The my exception', 123);
