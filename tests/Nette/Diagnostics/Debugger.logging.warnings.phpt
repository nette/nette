<?php

/**
 * Test: Nette\Diagnostics\Debugger notices and warnings logging.
 *
 * @author     David Grudl
 */

use Nette\Diagnostics\Debugger,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


// Setup environment
$logDirectory = TEMP_DIR . '/log';
Tester\Helpers::purge($logDirectory);

Debugger::$mailer = 'testMailer';

Debugger::enable(Debugger::PRODUCTION, $logDirectory, 'admin@example.com');

function testMailer() {}


// throw error
$a++;

Assert::match('%a%PHP Notice: Undefined variable: a in %a%', file_get_contents($logDirectory . '/error.log'));
Assert::true(is_file($logDirectory . '/email-sent'));
