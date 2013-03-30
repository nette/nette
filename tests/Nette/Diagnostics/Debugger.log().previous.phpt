<?php

/**
 * Test: Nette\Diagnostics\Debugger logging previous exceptions in log message.
 *
 * @author     David Grudl
 * @author     Michael Moravec
 * @package    Nette\Diagnostics
 */

use Nette\Diagnostics\Debugger;



require __DIR__ . '/../bootstrap.php';



// Setup environment
$_SERVER['HTTP_HOST'] = 'nette.org';

Debugger::$logDirectory = TEMP_DIR . '/log';
Tester\Helpers::purge(Debugger::$logDirectory);

Debugger::$mailer = 'testMailer';

function testMailer() {}


Debugger::enable(Debugger::DEVELOPMENT, NULL, 'admin@example.com');

header('Content-Type: text/plain');


$e = new Exception('First');


class TestLogger1
{
	public function log($message)
	{
		Assert::match('Exception: First in %a%:%d%.', $message[1]);
		return TRUE;
	}
}

Debugger::$logger = new TestLogger1();
Debugger::log($e);


$e = new InvalidArgumentException('Second', 0, $e);


class TestLogger2
{
	public function log($message)
	{
		Assert::match('InvalidArgumentException: Second in %a%:%d%, caused by Exception: First in %a%:%d%.', $message[1]);
		return TRUE;
	}
}

Debugger::$logger = new TestLogger2();
Debugger::log($e);


$e = new RuntimeException('Third', 0, $e);


class TestLogger3
{
	public function log($message)
	{
		Assert::match('RuntimeException: Third in %a%:%d%, caused by InvalidArgumentException: Second in %a%:%d%, caused by Exception: First in %a%:%d%.', $message[1]);
		return TRUE;
	}
}

Debugger::$logger = new TestLogger3();
Debugger::log($e);
