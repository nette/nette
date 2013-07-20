<?php

/**
 * Test: Nette\Diagnostics\Debugger error in toString.
 *
 * @author     David Grudl
 * @package    Nette\Diagnostics
 * @exitCode   254
 * @httpCode   500
 * @outputMatch OK!
 */

use Nette\Diagnostics\Debugger;


require __DIR__ . '/../bootstrap.php';


Debugger::$consoleMode = FALSE;
Debugger::$productionMode = FALSE;
header('Content-Type: text/html');

Debugger::enable();

register_shutdown_function(function(){
	Assert::match('%A%<title>User Error</title><!-- Test::__toString -->%A%', ob_get_clean());
	echo 'OK!';
});
ob_start();


class Test
{
	function __toString()
	{
		trigger_error(__METHOD__, E_USER_ERROR);
	}
}


echo new Test;
