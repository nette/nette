<?php

/**
 * Test: Nette\Diagnostics\Debugger Bar in non-HTML mode.
 *
 * @author     David Grudl
 * @package    Nette\Diagnostics
 * @outputMatch
 */

use Nette\Diagnostics\Debugger;


require __DIR__ . '/../bootstrap.php';


Debugger::$productionMode = FALSE;
header('Content-Type: text/plain');

Debugger::enable();
