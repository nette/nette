<?php

/**
 * Test: Nette\Diagnostics\Debugger logging exceptions in log message.
 *
 * @author     David Grudl
 * @author     Michael Moravec
 * @package    Nette\Diagnostics
 */

use Nette\Diagnostics\Debugger;


require __DIR__ . '/../bootstrap.php';


// Setup environment
Debugger::$logDirectory = TEMP_DIR . '/log';
Tester\Helpers::purge(Debugger::$logDirectory);


class TestLogger
{
	function __construct($pattern)
	{
		$this->pattern = $pattern;
	}

	public function log($message)
	{
		Assert::match($this->pattern, $message[1]);
	}
}


test(function() {
	Debugger::setLogger(new TestLogger('Exception: First in %a%:%d%'));
	$e = new Exception('First');
	Debugger::log($e);
});


test(function() {
	Debugger::setLogger(new TestLogger("RuntimeException: Third in %a%:%d%\ncaused by InvalidArgumentException: Second in %a%:%d%\ncaused by Exception: First in %a%:%d%"));
	$e = new Exception('First');
	$e = new InvalidArgumentException('Second', 0, $e);
	$e = new RuntimeException('Third', 0, $e);
	Debugger::log($e);
});
