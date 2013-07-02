<?php

/**
 * Test: Nette\Diagnostics\Debugger E_ERROR in console.
 *
 * @author     David Grudl
 * @package    Nette\Diagnostics
 */

require __DIR__ . '/../bootstrap.php';


$blueScreen = new Nette\Diagnostics\BlueScreen;

$blueScreen->collapsePaths[] = __DIR__;

Assert::true($blueScreen->isCollapsed(__FILE__));
Assert::false($blueScreen->isCollapsed(dirname(__DIR__) . 'somethingElse'));
