<?php

/**
 * Test: Nette\Diagnostics\Debugger autoloading.
 *
 * @author     David Grudl
 * @package    Nette\Diagnostics
 * @outputMatch %A%Strict Standards: Declaration of B::test() should be compatible with A::test() in %A%
 */

use Nette\Diagnostics\Debugger;


require __DIR__ . '/../bootstrap.php';


Debugger::$productionMode = FALSE;
header('Content-Type: text/plain');

Debugger::enable();


// in this case autoloading is not triggered
include 'E_STRICT.inc';
