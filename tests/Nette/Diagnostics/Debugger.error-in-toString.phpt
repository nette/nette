<?php

/**
 * Test: Nette\Diagnostics\Debugger error in toString.
 *
 * @author     David Grudl
 * @package    Nette\Diagnostics
 * @httpCode   500
 * @exitCode   254
 * @outputMatch %A%<title>User Error</title><!-- Test::__toString -->%A%
 */

use Nette\Diagnostics\Debugger;


require __DIR__ . '/../bootstrap.php';

if (PHP_SAPI === 'cli') {
	Tester\Environment::skip();
}


Debugger::$productionMode = FALSE;
header('Content-Type: text/html');

Debugger::enable();

class Test
{
	function __toString()
	{
		trigger_error(__METHOD__, E_USER_ERROR);
	}
}


echo new Test;
