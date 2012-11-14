<?php

/**
 * Test: Nette\Diagnostics\Debugger E_ERROR in console.
 *
 * @author     David Grudl
 * @package    Nette\Diagnostics
 * @subpackage UnitTests
 */

use Nette\Diagnostics\Debugger,
	\Nette\Diagnostics\Helpers;



require __DIR__ . '/../bootstrap.php';



Debugger::$consoleMode = TRUE;
Debugger::$productionMode = FALSE;

Debugger::$blueScreen->collapsePaths[] = __DIR__;

Assert::true(Helpers::isCollapsed(Debugger::$blueScreen, __FILE__));
Assert::false(Helpers::isCollapsed(Debugger::$blueScreen, dirname(__DIR__) . '/somethingElse'));
