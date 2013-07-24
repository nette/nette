<?php

/**
 * Test: Nette\Diagnostics\Debugger errors and shut-up operator.
 *
 * @author     David Grudl
 * @package    Nette\Diagnostics
 * @exitCode   255
 * @httpCode   500
 * @outputMatch exception 'Nette\FatalErrorException' with message 'Call to undefined function missing_funcion()' in %A%
 */

use Nette\Diagnostics\Debugger;


require __DIR__ . '/../bootstrap.php';


Debugger::$productionMode = FALSE;
header('Content-Type: text/plain');

Debugger::enable();

@missing_funcion();
