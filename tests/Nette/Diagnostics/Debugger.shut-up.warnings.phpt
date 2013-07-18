<?php

/**
 * Test: Nette\Diagnostics\Debugger notices and warnings and shut-up operator.
 *
 * @author     David Grudl
 * @package    Nette\Diagnostics
 * @outputMatch
 */

use Nette\Diagnostics\Debugger;


require __DIR__ . '/../bootstrap.php';


Debugger::$productionMode = FALSE;
header('Content-Type: text/plain; charset=utf-8');

Debugger::enable();

@mktime(); // E_STRICT
@mktime(0, 0, 0, 1, 23, 1978, 1); // E_DEPRECATED
@$x++; // E_NOTICE
@min(1); // E_WARNING
@require 'E_COMPILE_WARNING.inc'; // E_COMPILE_WARNING
