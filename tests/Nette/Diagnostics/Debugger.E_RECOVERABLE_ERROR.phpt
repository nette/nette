<?php

/**
 * Test: Nette\Diagnostics\Debugger E_RECOVERABLE_ERROR error.
 *
 * @author     David Grudl
 * @package    Nette\Diagnostics
 */

use Nette\Diagnostics\Debugger;


require __DIR__ . '/../bootstrap.php';


Debugger::$productionMode = FALSE;

Debugger::enable();


class TestClass
{

	function test1(array $val)
	{
	}


	function test2(TestClass $val)
	{
	}


	function __toString()
	{
		return FALSE;
	}


}


$obj = new TestClass;

Assert::exception(function() use ($obj) {
	// Invalid argument #1
	$obj->test1('hello');
}, 'Nette\FatalErrorException', 'Argument 1 passed to TestClass::test1() must be %a% array, string given, called in %a%');

Assert::exception(function() use ($obj) {
	// Invalid argument #2
	$obj->test2('hello');
}, 'Nette\FatalErrorException', 'Argument 1 passed to TestClass::test2() must be an instance of TestClass, string given, called in %a%');

Assert::exception(function() use ($obj) {
	// Invalid toString
	echo $obj;
}, 'Nette\FatalErrorException', 'Method TestClass::__toString() must return a string value');
