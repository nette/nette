<?php

/**
 * Test: Nette\Diagnostics\Debugger error logging.
 *
 * @author     David Grudl
 * @package    Nette\Diagnostics
 * @exitCode   255
 * @httpCode   500
 * @outputMatch %A%OK!
 */

use Nette\Diagnostics\Debugger;


require __DIR__ . '/../bootstrap.php';


// Setup environment
$_SERVER['HTTP_HOST'] = 'nette.org';

Debugger::$logDirectory = TEMP_DIR . '/log';
Tester\Helpers::purge(Debugger::$logDirectory);

Debugger::$mailer = function() {};

Debugger::enable(Debugger::PRODUCTION, NULL, 'admin@example.com');


register_shutdown_function(function() {
	Assert::match('%a%Fatal error: Call to undefined function missing_funcion() in %a%', file_get_contents(Debugger::$logDirectory . '/error.log'));
	Assert::true(is_file(Debugger::$logDirectory . '/email-sent'));
	echo 'OK!'; // prevents PHP bug #62725
});
ob_start();


missing_funcion();
